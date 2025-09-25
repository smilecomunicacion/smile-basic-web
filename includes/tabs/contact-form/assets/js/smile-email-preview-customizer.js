/* global sbwscfEmailPreviewData*/
// assets/js/smile-email-preview-customizer.js
// Define sbwscfEmailPreviewData object

;(function () {
	// Aseguramos que existe wp.customize.
	if (typeof wp === 'undefined' || !wp.customize) {
		return
	}

	wp.customize.section('sbwscf_email_appearance', function (section) {
		// Escuchamos el "expanded" event para saber cuando el usuario hace clic en la sección.
		section.expanded.bind(function (isExpanded) {
			if (isExpanded) {
				// Reemplazamos la URL de vista previa por la página en borrador.
				wp.customize.previewer.previewUrl.set(sbwscfEmailPreviewData.preview_url)
			}
		})
	})
})()
