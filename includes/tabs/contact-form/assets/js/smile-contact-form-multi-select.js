/**
 * File: js/smile-contact-form-multi-select.js
 *
 * Handles "select_multiple" menus with checkboxes.
 * Vanilla JavaScript, no jQuery dependency.
 *
 * @since 1.2.1
 */

document.addEventListener('DOMContentLoaded', () => {
	'use strict'

	// All multiselect containers.
	const containers = document.querySelectorAll('.sbwscf-multiselect-container')

	containers.forEach((container) => {
		const button = container.querySelector('.sbwscf-multiselect-button')
		const menu = container.querySelector('.sbwscf-multiselect-menu')
		if (!button || !menu) {
			return
		}

		// Fallback text if no <data‑placeholder> is provided.
		const placeholder = button.getAttribute('data-placeholder') || 'Select options'

		/**
		 * Updates the label of the multiselect button.
		 *
		 * @return {void}
		 */
		const updateButtonLabel = () => {
			const checked = menu.querySelectorAll('input[type="checkbox"]:checked')
			if (checked.length) {
				button.textContent = Array.from(checked)
					.map((cb) => cb.value)
					.join(', ')
			} else {
				button.textContent = placeholder
			}
		}

		// Initial state.
		updateButtonLabel()

		// Toggle menu visibility.
		button.addEventListener('click', (evt) => {
			evt.preventDefault()
			const expanded = button.getAttribute('aria-expanded') === 'true'
			button.setAttribute('aria-expanded', expanded ? 'false' : 'true')
			menu.style.display = expanded ? 'none' : 'block'
		})

		// Close when clicking outside.
		document.addEventListener('click', (evt) => {
			if (!container.contains(evt.target) && menu.style.display === 'block') {
				button.setAttribute('aria-expanded', 'false')
				menu.style.display = 'none'
			}
		})

		// Update label on checkbox change.
		menu.querySelectorAll('input[type="checkbox"]').forEach((cb) => {
			cb.addEventListener('change', updateButtonLabel)
		})
	})
})
