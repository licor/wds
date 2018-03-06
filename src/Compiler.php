<?php

namespace Wds;

use Symfony\Component\Finder\Finder;

class Compiler
{
	public function compile($pharFile = 'wds.phar') {

		if (file_exists($pharFile))
			unlink($pharFile);
			
        $phar = new \Phar($pharFile, 0, 'wds.phar');

        $phar->setSignatureAlgorithm(\Phar::SHA1);
        
        $phar->startBuffering();

		$finderSort = function ($a, $b) {
            return strcmp(strtr($a->getRealPath(), '\\', '/'), strtr($b->getRealPath(), '\\', '/'));
		};
		
		$finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
			->notName('Compiler.php')
			->in(__DIR__)
            ->sort($finderSort)
		;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }
        
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('LICENSE')
            ->exclude('Tests')
            ->exclude('tests')
            ->exclude('docs')
            ->exclude('*.json')
            ->in(__DIR__.'/../vendor/symfony/')
            // ->in(__DIR__.'/../vendor/psr/')
            ->in(__DIR__.'/../vendor/composer/')
            ->sort($finderSort)
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }


        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../vendor/autoload.php'));
        // $this->addFile($phar, new \SplFileInfo(__DIR__.'/../vendor/composer/autoload_namespaces.php'));
        // $this->addFile($phar, new \SplFileInfo(__DIR__.'/../vendor/composer/autoload_psr4.php'));
        // $this->addFile($phar, new \SplFileInfo(__DIR__.'/../vendor/composer/autoload_classmap.php'));
        // $this->addFile($phar, new \SplFileInfo(__DIR__.'/../vendor/composer/autoload_files.php'));
        // $this->addFile($phar, new \SplFileInfo(__DIR__.'/../vendor/composer/autoload_real.php'));
		// $this->addFile($phar, new \SplFileInfo(__DIR__.'/../vendor/composer/autoload_static.php'));
        // $this->addFile($phar, new \SplFileInfo(__DIR__.'/../vendor/composer/ClassLoader.php'));

		$this->addBin($phar);

        $phar->setStub($this->getStub());
        
        $phar->stopBuffering();

	}

    private function getRelativeFilePath($file) {
        $realPath = $file->getRealPath();
        $pathPrefix = (dirname(__DIR__)).DIRECTORY_SEPARATOR;
        $pos = strpos($realPath, $pathPrefix);
        $relativePath = ($pos !== false) ? substr_replace($realPath, '', $pos, strlen($pathPrefix)) : $realPath;
        return strtr($relativePath, '\\', '/');
    }

	private function addFile($phar, $file, $strip = true) {
        $path = $this->getRelativeFilePath($file);
        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
		}
		
        $phar->addFromString($path, $content);
    }

	private function stripWhitespace($source) {
        if (!function_exists('token_get_all')) {
            return $source;
        }
        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }
        return $output;
	}

    private function addBin($phar)
    {
        $content = file_get_contents(__DIR__.'/../bin/wds.php');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/wds.php', $content);
    }
	

	private function getStub()
    {
        $stub = <<<'EOF'
#!/usr/bin/env php
<?php
Phar::mapPhar('wds.phar');
require 'phar://wds.phar/bin/wds.php';
__HALT_COMPILER();
EOF;
	return $stub;
    }

}
