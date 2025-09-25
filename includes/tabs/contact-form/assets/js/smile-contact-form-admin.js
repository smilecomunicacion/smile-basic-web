/**
 * File: js/smile-contact-form-admin.js
 *
 * Maneja la funcionalidad de subir y remover el logo de la empresa
 *
 * @since 1.0
 */

/* global wp */
// phpcs:ignore WordPressVIPMinimum.JS.Global wp -- WP core object.

document.addEventListener('DOMContentLoaded', function () {
	const { __ } = wp.i18n // Local scope
	const uploadButton = document.getElementById('sbwscf_upload_logo')
	const removeButton = document.getElementById('sbwscf_remove_logo')
	const logoPreview = document.getElementById('sbwscf-logo-preview')
	const logoIdInput = document.getElementById('sbwscf_logo_id')

	let mediaUploader

	if (uploadButton) {
		uploadButton.addEventListener('click', function (e) {
			e.preventDefault()

			if (mediaUploader) {
				mediaUploader.open()
				return
			}

			mediaUploader = wp.media({
				title: __('Select Company Logo', 'smile-basic-web'),
				button: {
					text: __('Use this logo', 'smile-basic-web'),
				},
				multiple: false,
			})

			mediaUploader.on('select', function () {
				const attachment = mediaUploader.state().get('selection').first().toJSON()
				logoPreview.src = attachment.url
				logoPreview.style.display = 'block'
				logoIdInput.value = attachment.id
				removeButton.style.display = 'inline-block'
			})

			mediaUploader.open()
		})
	}

	if (removeButton) {
		removeButton.addEventListener('click', function (e) {
			e.preventDefault()
			logoPreview.src = ''
			logoPreview.style.display = 'none'
			logoIdInput.value = ''
			removeButton.style.display = 'none'
		})
	}

	// ReCAPTCHA conditional required fields
	const recaptchaCheckbox = document.querySelector(
		'input[name="sbwscf_settings[recaptcha_enabled]"]'
	)
	const siteKeyField = document.querySelector('input[name="sbwscf_settings[recaptcha_site_key]"]')
	const secretKeyField = document.querySelector(
		'input[name="sbwscf_settings[recaptcha_secret_key]"]'
	)

	function toggleRecaptchaRequired() {
		if (recaptchaCheckbox && recaptchaCheckbox.checked) {
			if (siteKeyField) siteKeyField.setAttribute('required', 'required')
			if (secretKeyField) secretKeyField.setAttribute('required', 'required')
		} else {
			if (siteKeyField) siteKeyField.removeAttribute('required')
			if (secretKeyField) secretKeyField.removeAttribute('required')
		}
	}

	if (recaptchaCheckbox) {
		recaptchaCheckbox.addEventListener('change', toggleRecaptchaRequired)
		toggleRecaptchaRequired() // Ejecutar al cargar para establecer el estado inicial
	}
})
