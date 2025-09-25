/**
 * Admin JS for Cookies settings.
 *
 * @package smile-basic-web
 */

document.addEventListener('DOMContentLoaded', () => {
	// Intentional no-op: native <input type="color"> handles color picking.
	const table = document.querySelector('#sbwscf-tracking-scripts-table tbody')
	const addBtn = document.getElementById('sbwscf-add-script')

	addBtn.addEventListener('click', () => {
		const rowCount = table.rows.length
		const row = document.createElement('tr')

		row.innerHTML = `
				<td><input type="text" name="sbwscf_tracking_scripts[${rowCount}][name]" class="regular-text" /></td>
				<td><input type="text" name="sbwscf_tracking_scripts[${rowCount}][description]" class="regular-text" /></td>
				<td><textarea name="sbwscf_tracking_scripts[${rowCount}][code]" rows="4" class="regular-text code"></textarea></td>
				<td><button type="button" class="button sbwscf-remove-script">Delete</button></td>
			`

		table.appendChild(row)
	})

	table.addEventListener('click', (event) => {
		if (event.target.classList.contains('sbwscf-remove-script')) {
			event.target.closest('tr').remove()
		}
	})
})
