/**
 * File: js/smile-contact-form.js
 *
 * Handles front‑end submission (AJAX + reCAPTCHA v3), select _single UI,
 * character counters, and graceful reset.
 * La lógica de “select_multiple” se delega ahora al archivo
 *   smile-contact-form-multi-select.js
 *
 * @since 1.2.3
 */

'use strict'

document.addEventListener('DOMContentLoaded', () => {
	/* global grecaptcha, wp, sbwscfAjaxObject */
	const { __, sprintf } = wp.i18n // local scope
	/* ---------------------------------------------------------------------
	 * Basic refs
	 * ------------------------------------------------------------------ */
	const form = document.getElementById('sbwscf-form')
	const messagesContainer = document.getElementById('sbwscf-form-messages')
	const recaptchaEnabled = sbwscfAjaxObject.recaptcha_enabled
	const recaptchaSiteKey = sbwscfAjaxObject.recaptcha_site_key

	if (!form || !messagesContainer) {
		return // No form on this page.
	}

	/* ---------------------------------------------------------------------
	 * Character counters for <textarea>.
	 * ------------------------------------------------------------------ */

	function initializeCharCounters() {
		document.querySelectorAll('.sbwscf-char-counter').forEach((counter) => {
			const textarea = counter.previousElementSibling
			if (!textarea) {
				return
			}

			const max = parseInt(counter.dataset.max || '1000', 10)

			const update = () => {
				const current = textarea.value.length
				counter.textContent = `${current} / ${max}`

				if (current > max) {
					counter.style.color = 'red'
					textarea.setCustomValidity(
						sprintf(
							/* translators: %d is the maximum allowed characters. */
							__('Character limit of %d exceeded.', 'smile-basic-web'),
							max
						)
					)
				} else {
					counter.style.color = '#666666'
					textarea.setCustomValidity('')
				}
			}

			update()
			textarea.addEventListener('input', update)
		})
	}

	function resetCharCounters() {
		document.querySelectorAll('.sbwscf-char-counter').forEach((counter) => {
			const textarea = counter.previousElementSibling
			if (!textarea) {
				return
			}
			const max = counter.dataset.max || '1000'
			counter.textContent = `0 / ${max}`
			counter.style.color = '#666666'
			textarea.setCustomValidity('')
		})
	}

	/* ---------------------------------------------------------------------
	 * “select_single” UI helpers
	 * ------------------------------------------------------------------ */

	function closeAllSelectMenus() {
		document
			.querySelectorAll('.sbwscf-select-single-button')
			.forEach((btn) => btn.setAttribute('aria-expanded', 'false'))

		document
			.querySelectorAll('.sbwscf-select-single-menu')
			.forEach((menu) => (menu.style.display = 'none'))
	}

	function initializeSelectMenus() {
		document.querySelectorAll('.sbwscf-select-single').forEach((container) => {
			const button = container.querySelector('.sbwscf-select-single-button')
			const menu = container.querySelector('.sbwscf-select-single-menu')
			if (!button || !menu) {
				return
			}

			button.addEventListener('click', (e) => {
				e.stopPropagation()
				const expanded = button.getAttribute('aria-expanded') === 'true'
				button.setAttribute('aria-expanded', expanded ? 'false' : 'true')
				menu.style.display = expanded ? 'none' : 'block'
			})

			menu.querySelectorAll('input[type="radio"]').forEach((radio) => {
				radio.addEventListener('change', function () {
					if (this.checked) {
						button.textContent = this.parentElement.textContent.trim()
						button.setAttribute('aria-expanded', 'false')
						menu.style.display = 'none'
					}
				})
			})
		})
	}

	// Close open menus when clicking elsewhere.
	document.addEventListener('click', (evt) => {
		if (!evt.target.closest('.sbwscf-select-single')) {
			closeAllSelectMenus()
		}
	})

	initializeCharCounters()
	initializeSelectMenus()

	/* ---------------------------------------------------------------------
	 * Form submission
	 * ------------------------------------------------------------------ */

	form.addEventListener('submit', (evt) => {
		// Native HTML 5 validation first.
		if (!form.checkValidity()) {
			evt.preventDefault()
			form.reportValidity()
			return
		}

		evt.preventDefault()

		const submitBtn = form.querySelector('[type="submit"]')
		if (submitBtn) {
			submitBtn.disabled = true
		}
		messagesContainer.innerHTML = ''

		const proceed = (token) => sendFormWithToken(token || '')

		if (recaptchaEnabled && recaptchaSiteKey && typeof grecaptcha !== 'undefined') {
			grecaptcha
				.execute(recaptchaSiteKey, { action: 'submit' })
				.then(proceed)
				.catch(() => {
					messagesContainer.innerHTML =
						'<p class="sbwscf-message-error">' +
						sbwscfAjaxObject.recaptcha_execute_error +
						'</p>'
					if (submitBtn) {
						submitBtn.disabled = false
					}
				})
		} else {
			proceed('')
		}
	})

	/**
	 * Sends the form through AJAX and handles the JSON reply.
	 *
	 * @param {string} recaptchaToken Empty if reCAPTCHA is not used.
	 * @return {void}
	 */
	function sendFormWithToken(recaptchaToken) {
		const formData = new FormData(form)
		formData.append('action', 'submit_smile_contact_form')
		if (recaptchaToken) {
			formData.append('g-recaptcha-response', recaptchaToken)
		}

		fetch(sbwscfAjaxObject.ajax_url, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		})
			.then((response) => response.json())
			.then((data) => {
				const submitBtn = form.querySelector('[type="submit"]')
				if (data.success) {
					messagesContainer.innerHTML =
						'<p class="sbwscf-message-success">' + data.data + '</p>'
					form.reset()
					closeAllSelectMenus()
					resetCharCounters()
				} else {
					messagesContainer.innerHTML =
						'<p class="sbwscf-message-error">' + data.data + '</p>'
				}
				if (submitBtn) {
					submitBtn.disabled = false
				}
			})
			.catch(() => {
				messagesContainer.innerHTML =
					'<p class="sbwscf-message-error">' + sbwscfAjaxObject.error_message + '</p>'
				const submitBtn = form.querySelector('[type="submit"]')
				if (submitBtn) {
					submitBtn.disabled = false
				}
			})
	}
})
