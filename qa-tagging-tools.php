<?php
/*
	Question2Answer Tagging Tools plugin
	License: http://www.gnu.org/licenses/gpl.html
*/

require_once QA_INCLUDE_DIR.'qa-app-posts.php';
require_once 'qa-tt-helper.php';

class qa_tagging_tools
{
	private $directory;
	private $urltoroot;

	public function load_module($directory, $urltoroot)
	{
		$this->directory = $directory;
		$this->urltoroot = $urltoroot;
	}

	public function option_default($option)
	{
		switch ($option) {
			case 'tagging_tools_synonyms':
				return '';
			case 'tagging_tools_prevent':
				return 0;
			case 'tagging_tools_rep':
				return 100;
		}
	}

	public function admin_form( &$qa_content )
	{
		// process config change
		$saved_msg = '';
		$js = array();

		if ( qa_clicked('tagging_tools_save_button') )
		{
			qa_opt( 'tagging_tools_synonyms', strtolower(trim(qa_post_text('tagging_tools_synonyms'))) );
			qa_opt( 'tagging_tools_prevent', (int) qa_post_text('tagging_tools_prevent') );
			qa_opt( 'tagging_tools_rep', (int) qa_post_text('tagging_tools_rep') );
			$saved_msg = '<div id="tagging_tools_recalc">Tag Synonyms settings saved</div>';

			// convert all old tags based on synonyms
			if ( qa_post_text('tagging_tools_convert') )
			{
				$saved_msg = '<div id="tagging_tools_recalc">Editing tags...</div>';
				$js = array(
					'<script>',
					'/* ajax request to "ajax-tagging-tools" */',
					'function ajax_retag()',
					'{',
					'	$.ajax({',
					'		url: qa_root+"ajax-tagging-tools",',
					'		success: function(response) {',
// 					'			console.log(response);',
					'			var posts_left = parseInt(response,10);',
					'			var $ok = $("#tagging_tools_recalc");',
					'			if ( posts_left === 0 ) {',
					'				$ok.text("All tags edited!");',
					'			}',
					'			else if ( isNaN(posts_left) ) {',
					'				$ok.text("There was an error editing the tags.");',
					'			}',
					'			else {',
					'				$ok.text("Editing tags... "+posts_left+" posts remaining...");',
					'				window.setTimeout(ajax_retag, 1500);',
					'			}',
					'			',
					'		}',
					'	});',
					'}',
					'$(window).load(ajax_retag);',
					'</script>',
				);
			}
		}

		// set fields to show/hide when checkbox is clicked
		qa_set_display_rules($qa_content, array(
			'tagging_tools_rep' => 'tagging_tools_prevent',
		));

		return array(
			'ok' => $saved_msg,

			'fields' => array(
				array(
					'label' => 'Tag Synonyms',
					'tags' => 'name="tagging_tools_synonyms" id="tagging_tools_synonyms"',
					'value' => qa_opt('tagging_tools_synonyms'),
					'type' => 'textarea',
					'rows' => 20,
					'note' => 'Put each pair of synonyms on a new line. <code>q2a,question2answer</code> means that a tag of <code>q2a</code> will be replaced by <code>question2answer</code>, while <code>help</code> on its own means that tag will be removed.',
				),
				array(
					'label' => 'Also convert existing tags using above rules',
					'tags' => 'name="tagging_tools_convert" id="tagging_tools_convert"',
					'value' => '',
					'type' => 'checkbox',
				),

				array(
					'label' => 'Prevent new users from creating new tags',
					'tags' => 'name="tagging_tools_prevent" id="tagging_tools_prevent"',
					'value' => qa_opt('tagging_tools_prevent'),
					'type' => 'checkbox',
				),

				array(
					'id' => 'tagging_tools_rep',
					'label' => 'Minimum reputation to create new tags',
					'value' => qa_opt('tagging_tools_rep'),
					'tags' => 'name="tagging_tools_rep"',
					'type' => 'number',
				),

				array(
					'type' => 'custom',
					'html' => implode("\n", $js)."\n",
				),
			),

			'buttons' => array(
				array(
					'label' => 'Save Changes',
					'tags' => 'name="tagging_tools_save_button"',
				),
			),
		);
	}
}
