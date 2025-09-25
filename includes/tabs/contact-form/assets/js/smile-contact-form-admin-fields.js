/* global sbwscfAdminFields, wp */
/**
 * File: js/smile-contact-form-admin-fields.js.
 *
 * Maneja la funcionalidad de agregar, eliminar y reordenar campos personalizados
 * en la página de configuración del plugin SMiLE Contact Form.
 *
 * @since 1.0
 */

document.addEventListener('DOMContentLoaded', function () {
	'use strict'

	const addFieldButton = document.getElementById('sbwscf-add-field')
	const fieldsContainer = document.getElementById('sbwscf-custom-fields-container')

	if (addFieldButton && fieldsContainer) {
		addFieldButton.addEventListener('click', function (e) {
			e.preventDefault()

			const tableBody = fieldsContainer.querySelector('table tbody')
			let maxIndex = -1
			const rows = tableBody.querySelectorAll('tr')
			rows.forEach((row) => {
				const anyInput = row.querySelector('input, select')
				if (anyInput) {
					const match = anyInput.name.match(/sbwscf_custom_fields\[(\d+)\]/)
					if (match && match[1]) {
						const foundIndex = parseInt(match[1], 10)
						if (!isNaN(foundIndex) && foundIndex > maxIndex) {
							maxIndex = foundIndex
						}
					}
				}
			})

			const newIndex = maxIndex + 1

			// Creación de la nueva fila:
			const newRow = document.createElement('tr')
			newRow.innerHTML = `
				<!-- Col 1: Order -->
				<td>
					<button type="button" class="button button-secondary sbwscf-move-up"
						title="${sbwscfAdminFields.move_up_title}">
						&#x25B2;
					</button>
					<button type="button" class="button button-secondary sbwscf-move-down"
						title="${sbwscfAdminFields.move_down_title}">
						&#x25BC;
					</button>
				</td>
				<!-- Col 2: Label -->
				<td>
					<input type="text"
						name="sbwscf_custom_fields[${newIndex}][label]"
						class="regular-text"
						required>
				</td>
				<!-- Col 3: Name -->
				<td>
					<input type="text"
						name="sbwscf_custom_fields[${newIndex}][name]"
						class="regular-text"
						required>
				</td>
				<!-- Col 4: Type -->
				<td>
					<select name="sbwscf_custom_fields[${newIndex}][type]" required>
						<option value="text">${sbwscfAdminFields.type_text}</option>
						<option value="email">${sbwscfAdminFields.type_email}</option>
						<option value="textarea">${sbwscfAdminFields.type_textarea}</option>
						<option value="number">${sbwscfAdminFields.type_number}</option>
						<option value="url">${sbwscfAdminFields.type_url}</option>
						<option value="tel">${sbwscfAdminFields.type_tel}</option>
						<option value="select_single">Select (Single)</option>
						<option value="select_multiple">Select (Multiple)</option>
					</select>
				</td>
				<!-- Col 5: Required -->
				<td>
					<input type="checkbox"
						name="sbwscf_custom_fields[${newIndex}][required]"
						value="1">
				</td>
				<!-- Col 6: Placeholder -->
				<td>
					<input type="text"
						name="sbwscf_custom_fields[${newIndex}][placeholder]"
						class="regular-text"
						value="">
				</td>
				<!-- Col 7: Options -->
				<td>
					<input type="text"
						name="sbwscf_custom_fields[${newIndex}][options]"
						class="regular-text"
						value="">
					<p class="description" style="margin:0;">
						${sbwscfAdminFields.options_description}
					</p>
				</td>
				<!-- Col 8: Actions (Delete) -->
				<td>
					<button type="button" class="button button-secondary sbwscf-remove-field">
						${sbwscfAdminFields.remove_button_text}
					</button>
				</td>
			`

			tableBody.appendChild(newRow)
		})
	}

	if (fieldsContainer) {
		fieldsContainer.addEventListener('click', function (e) {
			// Eliminar campo.
			if (e.target && e.target.classList.contains('sbwscf-remove-field')) {
				e.preventDefault()
				const row = e.target.closest('tr')
				if (row) {
					row.remove()
					reindexAllRows()
				}
			}

			// Mover arriba.
			if (e.target && e.target.classList.contains('sbwscf-move-up')) {
				e.preventDefault()
				const row = e.target.closest('tr')
				const prevRow = row && row.previousElementSibling
				if (row && prevRow) {
					row.parentNode.insertBefore(row, prevRow)
					reindexAllRows()
				}
			}

			// Mover abajo.
			if (e.target && e.target.classList.contains('sbwscf-move-down')) {
				e.preventDefault()
				const row = e.target.closest('tr')
				const nextRow = row && row.nextElementSibling
				if (row && nextRow) {
					row.parentNode.insertBefore(nextRow, row)
					reindexAllRows()
				}
			}
		})
	}

	/**
	 * Reindexa todos los rows para que los name="sbwscf_custom_fields[X]..."
	 * queden en orden (0..n).
	 */
	function reindexAllRows() {
		const tableBody = fieldsContainer.querySelector('table tbody')
		const rows = tableBody.querySelectorAll('tr')

		rows.forEach(function (row, newIndex) {
			const inputs = row.querySelectorAll('input, select, textarea')
			inputs.forEach(function (el) {
				const match = el.name.match(/sbwscf_custom_fields\[\d+\]\[(.+)\]/)
				if (match && match[1]) {
					const subFieldName = match[1]
					el.name = `sbwscf_custom_fields[${newIndex}][${subFieldName}]`
				}
			})
		})
	}
})
