/**
 * Admin JS for Cookies settings.
 *
 * @package smile-basic-web
 */

document.addEventListener('DOMContentLoaded', () => {
        const tableBody = document.querySelector('#sbwscf-tracking-scripts-table tbody')
        const addBtn = document.getElementById('sbwscf-add-script')

        if (!tableBody || !addBtn) {
                return
        }

        const hasI18n = window.wp && window.wp.i18n && typeof window.wp.i18n.__ === 'function'
        const __ = hasI18n ? window.wp.i18n.__ : (text) => text
        const editorSettings = window.sbwscfTrackingEditorSettings ? window.sbwscfTrackingEditorSettings : {}

        let nextIndex = Array.from(tableBody.rows).reduce((max, row) => {
                const index = parseInt(row.dataset.rowIndex || '', 10)

                if (Number.isNaN(index)) {
                        return max
                }

                return Math.max(max, index)
        }, -1) + 1

        const cloneSettings = (settings) => {
                if (!settings) {
                        return {}
                }

                try {
                        return JSON.parse(JSON.stringify(settings))
                } catch (error) {
                        return { ...settings }
                }
        }

        const initializeEditor = (editorId, settings) => {
                if (!window.wp || !window.wp.editor || 'function' !== typeof window.wp.editor.initialize) {
                        return
                }

                window.wp.editor.initialize(editorId, cloneSettings(settings))
        }

        const addRow = (index) => {
                const nameId = `sbwscf_tracking_scripts_${index}_name`
                const descriptionId = `sbwscf_tracking_scripts_${index}_description`
                const codeId = `sbwscf_tracking_scripts_${index}_code`

                const row = document.createElement('tr')
                row.dataset.rowIndex = index
                row.dataset.nameEditor = nameId
                row.dataset.descriptionEditor = descriptionId

                row.innerHTML = `
                        <td><textarea id="${nameId}" name="sbwscf_tracking_scripts[${index}][name]" rows="2" class="regular-text"></textarea></td>
                        <td><textarea id="${descriptionId}" name="sbwscf_tracking_scripts[${index}][description]" rows="6" class="regular-text"></textarea></td>
                        <td><textarea id="${codeId}" name="sbwscf_tracking_scripts[${index}][code]" rows="4" class="regular-text code"></textarea></td>
                        <td><button type="button" class="button sbwscf-remove-script">${__( 'Delete', 'smile-basic-web' )}</button></td>
                `

                tableBody.appendChild(row)

                initializeEditor(nameId, editorSettings.name)
                initializeEditor(descriptionId, editorSettings.description)
        }

        addBtn.addEventListener('click', () => {
                addRow(nextIndex)
                nextIndex += 1
        })

        tableBody.addEventListener('click', (event) => {
                const target = event.target

                if (!target || !target.classList.contains('sbwscf-remove-script')) {
                        return
                }

                const row = target.closest('tr')

                if (!row) {
                        return
                }

                const nameEditorId = row.dataset.nameEditor
                const descriptionEditorId = row.dataset.descriptionEditor

                if (nameEditorId && window.wp && window.wp.editor && 'function' === typeof window.wp.editor.remove) {
                        window.wp.editor.remove(nameEditorId)
                }

                if (descriptionEditorId && window.wp && window.wp.editor && 'function' === typeof window.wp.editor.remove) {
                        window.wp.editor.remove(descriptionEditorId)
                }

                row.remove()
        })
})
