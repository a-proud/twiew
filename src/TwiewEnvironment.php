<?php

namespace AProud\Twiew;

/**
 * Override of \Twig\Environment class. Provide alternative twig syntax for .tw.twig files.
 */
class TwiewEnvironment extends \Twig\Environment
{
	private $altLexerInstance;
	
	/**
	 * @return \Twig\Lexer instance with alternative syntax
	 */
	private function getAltLexer(): \Twig\Lexer
	{
		if (empty($this->altLexer)) {
			$this->altLexerInstance = new \Twig\Lexer($this, [
				'tag_comment' => ['{@tw:#', '#}'],
				'tag_block' => ['{@tw:%', '%}'],
				'tag_variable' => ['{@tw:{', '}}'],
				'interpolation' => ['#@tw:{', '}'],
			]);
		}
		return $this->altLexerInstance;
	}

	/**
	 * \Twig\Template loadTemplate method override. Provide alternative twig syntax for files with .tw.twig extension.
	 */
	public function compileSource(\Twig\Source $source): string
    {
        try {
        	if (str_contains($source->getName(), '.tw.twig') ) {
				$this->setLexer($this->getAltLexer());
			}
            return $this->compile($this->parse($this->tokenize($source)));
        } catch (Error $e) {
            $e->setSourceContext($source);
            throw $e;
        } catch (\Exception $e) {
            throw new SyntaxError(sprintf('An exception has been thrown during the compilation of a template ("%s").', $e->getMessage()), -1, $source, $e);
        }
    }

}