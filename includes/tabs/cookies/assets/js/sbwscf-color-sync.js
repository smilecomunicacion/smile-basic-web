/* ------------------------------------------------------------------
 * Colour picker synchroniser
 * ------------------------------------------------------------------
 */
document.addEventListener('DOMContentLoaded', () => {
	'use strict'

	const wrappers = document.querySelectorAll('.sbwscf-color-wrapper')

	/**
	 * Validate a HEX string (#abc o #aabbcc).
	 *
	 * @param {string} hex
	 * @return {boolean}
	 */
	const isValidHex = (hex) => /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(hex)

	wrappers.forEach((wrapper) => {
		const preview = /** @type {HTMLInputElement} */ (
			wrapper.querySelector('.sbwscf-color-preview')
		)
		const hexInput = /** @type {HTMLInputElement} */ (
			wrapper.querySelector('.sbwscf-color-hex')
		)

		if (!preview || !hexInput) {
			return
		}

		/* Sincroniza preview → input. */
		preview.addEventListener('input', () => {
			hexInput.value = preview.value
		})

		/* Sincroniza input → preview (incluye pegar). */
		const updatePreview = () => {
			const hex = hexInput.value.trim()
			if (isValidHex(hex)) {
				preview.value = hex
			}
		}
		hexInput.addEventListener('input', updatePreview)
		hexInput.addEventListener('paste', () => {
			setTimeout(updatePreview, 0)
		})
	})
})
