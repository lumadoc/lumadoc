<?php

	namespace Lumadoc;

	use Latte;


	class LatteMacros
	{
		public static function install(Latte\Compiler $compiler)
		{
			$set = new Latte\Macros\MacroSet($compiler);

			$set->addMacro(
				'doc-href',
				NULL,
				NULL,
				function (Latte\MacroNode $node, Latte\PhpWriter $writer) {
					return $writer->write(' ?> href="<?php echo %escape(%modify(call_user_func($this->global->lumadocPageLinker, %node.word))); ?>"<?php ');
				}
			);
		}
	}
