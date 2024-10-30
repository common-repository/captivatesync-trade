/**
 * QuilJs
 * Used to generate our QuilJs powered shownotes section.
 */

jQuery(document).ready(function($){

	/**
	 * Episode show notes quill
	 */
	var publish_episode_screens = ['toplevel_page_cfm-hosting-publish-episode', 'admin_page_cfm-hosting-publish-episode', 'captivate-sync_page_cfm-hosting-publish-episode'];

	var quill = '',
		quill_container = '#cfm-field-wpeditor';

	if ( $( quill_container ).length ) {

		quill = new Quill(
			quill_container,
			{
				modules: {
					toolbar: '#quilljs-toolbar'
				},
				placeholder: 'Insert text here ...',
				theme: 'snow'
			}
		);

		var form = document.querySelector( '#cfm-form-publish-episode' );

		form.onsubmit = function() {
			var ql_editor = $(quill_container),
				ql_html = ql_editor.find('.ql-editor').html();

			// Populate hidden form on submit.
			var ql_post_content = document.querySelector( 'textarea[name=post_content]' );
			ql_post_content.value = ql_html;
		};

		quill.on(
			'text-change',
			function(delta, source) {
				var ql_editor = $(quill_container),
					ql_html = ql_editor.find('.ql-editor').html();

				if ( ql_html != '' && ql_html != '<p><br></p>' ) {
					$( '#cfm-field-wpeditor' ).removeClass( 'cfm-field-error' );
					$( '.cfm-show-description .ql-toolbar.ql-snow' ).removeClass( 'cfm-field-error' );
					$( '#shownotes-error' ).remove();
				}

				// LOCALSTORAGE - save custom localstorage.
				if( $.inArray( cfmsync.CFMH_CURRENT_SCREEN, publish_episode_screens) !== -1) {
					localStorage.setItem(cfmsync.CFMH_SHOWID + '_shownotes_local', JSON.stringify(quill.getContents()));
					localStorage.setItem(cfmsync.CFMH_SHOWID + '_shownotes_local_html', ql_html);
				}
			}
		);

		// LOCALSTORAGE - populate custom localstorage.
		if( $.inArray( cfmsync.CFMH_CURRENT_SCREEN, publish_episode_screens) !== -1) {
			const shownotes_local = localStorage.getItem(cfmsync.CFMH_SHOWID + '_shownotes_local');
			quill.setContents(JSON.parse(shownotes_local));
		}

	}

	/**
	 * Checks quill content
	 */
	function isQuillEmpty(quill) {
	  if ((quill.getContents()['ops'] || []).length !== 1) { return false }
	  return quill.getText().trim().length === 0
	}

});
