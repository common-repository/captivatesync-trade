jQuery( document ).ready(
	function( $ ) {

			/**
			 * Default
			 */
			$( '#cfm-datatable' ).DataTable(
				{
					searching: false,
					ordering:  true,
					bInfo:  false,
					bLengthChange: false,
					bFilter: true,
					bAutoWidth: false,
					pageLength: 20,
					fnDrawCallback: function() {
						 var paginateRow = $( 'div.dataTables_paginate' );
						 var pageCount   = Math.ceil( (this.fnSettings().fnRecordsDisplay()) / this.fnSettings()._iDisplayLength );

						if ( pageCount > 1 ) {
							paginateRow.css( "display", "block" );
						} else {
							paginateRow.css( "display", "none" );
						}
					}
				}
			);

			/**
			 * Podcast Episodes
			 */
			$( "#cfm-datatable-episodes" ).one(
				"preInit.dt",
				function () {

					$( "#cfm-datatable-episodes_filter" ).prepend( '<a href="' + cfmsync.CFMH_ADMINURL + 'admin.php?page=cfm-hosting-publish-episode&show_id=' + cfmsync.CFMH_SHOWID + '" class="btn btn-lg btn-primary float-left">Publish Episode</a>' );

				}
			);

			$( '#cfm-datatable-episodes' ).DataTable(
				{
					searching: true,
					ordering:  true,
					bInfo:  true,
					bLengthChange: false,
					bFilter: true,
					bAutoWidth: false,
					pageLength: 20,
					order: [[ 2, "desc" ]],
					columnDefs: [
					{bSortable: false, targets: [3,4,5]}
					],
					language: {
						paginate: {
							next: '<i class="fas fa-arrow-right"></i>', // or '→'.
							previous: '<i class="fas fa-arrow-left"></i>' // or '←'.
						},
						search: '',
						searchPlaceholder: 'Search...'
					},
					fnInfoCallback: function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
						return iStart + " to " + iEnd + " of " + iTotal;
					},
					fnDrawCallback: function() {
						var paginateRow = $( 'div.dataTables_paginate' );
						var pageCount   = Math.ceil( (this.fnSettings().fnRecordsDisplay()) / this.fnSettings()._iDisplayLength );

						if ( pageCount > 1 ) {
							paginateRow.css( "display", "block" );
						} else {
							paginateRow.css( "display", "none" );
						}
					}
				}
			);

	}
);
