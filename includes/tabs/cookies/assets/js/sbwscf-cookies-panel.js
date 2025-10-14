/* global wp, sbwscfCookieScripts */
/**
 * File: sbwscf-cookies-panel.js
 *
 * JavaScript para el panel de Cookies.
 *
 * @since 1.0.0
 */

document.addEventListener('DOMContentLoaded', function () {
	const panel = document.getElementById('sbwscf-smile-cookies-panel')
	const manageBtn = document.getElementById('sbwscf-manage-consent-btn')
	const container = document.getElementById('sbwscf-manage-consent-container')
	const acceptBtn = panel?.querySelector('.sbwscf-smile-cookies-accept')
	const denyBtn = panel?.querySelector('.sbwscf-smile-cookies-deny')
	const preferencesBtn = panel?.querySelector('.sbwscf-smile-cookies-preferences')
	const preferenceInputs = document.querySelectorAll('[data-category]')
	const categoriesBox = document.getElementById('sbwscf-cookie-categories')

        const preferencesDefaultLabel = preferencesBtn?.dataset?.preferencesLabel || wp.i18n.__('Preferences', 'smile-basic-web')
        const preferencesAcceptLabel = preferencesBtn?.dataset?.acceptPreferencesLabel || wp.i18n.__('Accept Preferences', 'smile-basic-web')
        const acceptInitialLabel = acceptBtn?.dataset?.initialLabel || wp.i18n.__('Accept', 'smile-basic-web')
        const acceptAllLabel = acceptBtn?.dataset?.allLabel || wp.i18n.__('Accept All', 'smile-basic-web')
        const denyInitialLabel = denyBtn?.dataset?.initialLabel || wp.i18n.__('Deny', 'smile-basic-web')
        const denyAllLabel = denyBtn?.dataset?.allLabel || wp.i18n.__('Deny All', 'smile-basic-web')

        function minimizePanel() {
                if (panel) panel.style.display = 'none'
                if (container) container.style.display = ''
        }

        function restorePanel() {
                const status = localStorage.getItem('sbwscf-cookie-status')

                if (panel) {
                        panel.style.display = 'flex'
                }
                if (container) container.style.display = 'none'

                if (categoriesBox) {
                        categoriesBox.hidden = !status
                }

                if (preferencesBtn) {
                        preferencesBtn.textContent = status
                                ? preferencesAcceptLabel
                                : preferencesDefaultLabel
                }

                matchButtonWidths()
        }

	function savePreferences() {
		const prefs = {}
		preferenceInputs.forEach((input) => {
			prefs[input.dataset.category] = input.checked
		})
		localStorage.setItem('sbwscf-cookie-preferences', JSON.stringify(prefs))
	}

	function loadPreferences() {
		const saved = localStorage.getItem('sbwscf-cookie-preferences')
		if (!saved) return
		try {
			const prefs = JSON.parse(saved)
			preferenceInputs.forEach((input) => {
				const state = prefs[input.dataset.category]
				if (typeof state === 'boolean') {
					input.checked = state
				}
			})
		} catch (e) {
			console.error('Invalid preferences JSON', e)
		}
	}

	function injectScripts() {
		const consent = localStorage.getItem('sbwscf-cookie-status')
		const saved = localStorage.getItem('sbwscf-cookie-preferences')

		if (typeof sbwscfCookieScripts !== 'undefined') {
			sbwscfCookieScripts.scripts.forEach((script) => {
				const existing = document.querySelector(
					'script[data-sbwscf-id="' + script.category + '"]'
				)
				if (existing) existing.remove()
			})
		}

		if (consent !== 'accepted' || !saved || typeof sbwscfCookieScripts === 'undefined') {
			return
		}

		try {
			const prefs = JSON.parse(saved)
			sbwscfCookieScripts.scripts.forEach((script) => {
				if (prefs[script.category]) {
					const tag = document.createElement('script')
					tag.type = 'text/javascript'
					tag.setAttribute('data-sbwscf-id', script.category)
					tag.text = script.code
					document.head.appendChild(tag)
				}
			})
		} catch (e) {
			console.error('Error injecting scripts', e)
		}
	}

	// --- Botón “Accept All” (antes “Accept”) ---
	if (acceptBtn) {
		acceptBtn.addEventListener('click', function () {
			// Marcar **todos** los checkboxes opcionales
			const allBoxes = document.querySelectorAll(
				'#sbwscf-cookie-categories input[type="checkbox"]:not(:disabled)'
			)
			allBoxes.forEach((cb) => {
				cb.checked = true
			})

			// Guardar estado “accepted”
			localStorage.setItem('sbwscf-cookie-status', 'accepted')

			// Recopilar y guardar preferencias
			const prefs = {}
			allBoxes.forEach((cb) => {
				const cat = cb.dataset.category
				if (cat) prefs[cat] = cb.checked
			})
			localStorage.setItem('sbwscf-cookie-preferences', JSON.stringify(prefs))
			savePreferences()

			injectScripts()
			if (categoriesBox) categoriesBox.hidden = true
			minimizePanel()
		})
	}

	// --- Botón “Deny All” (antes “Deny”) ---
	if (denyBtn) {
		denyBtn.addEventListener('click', function () {
			localStorage.setItem('sbwscf-cookie-status', 'denied')

			const allBoxes = document.querySelectorAll(
				'#sbwscf-cookie-categories input[type="checkbox"]:not(:disabled)'
			)
			const prefs = {}
			allBoxes.forEach((cb) => {
				cb.checked = false
				const cat = cb.dataset.category
				if (cat) prefs[cat] = false
			})
			localStorage.setItem('sbwscf-cookie-preferences', JSON.stringify(prefs))
			savePreferences()

			injectScripts()
			if (categoriesBox) categoriesBox.hidden = true
			minimizePanel()
		})
	}

	// --- Botón “Manage Consent” (minimizado) ---
	if (manageBtn) {
		manageBtn.addEventListener('click', restorePanel)
	}

	/*
	 * ------------------------------------------------------------------
	 * Botón “Preferences” / “Accept Preferences”
	 * ------------------------------------------------------------------
	 */
	if (preferencesBtn) {
		preferencesBtn.addEventListener('click', function () {
			// Si el contenedor está oculto, lo mostramos y cambiamos a “Accept Preferences”
			if (categoriesBox.hidden) {
				categoriesBox.hidden = false

                                preferencesBtn.textContent = preferencesAcceptLabel

				// Recalcular anchos para el nuevo texto
				matchButtonWidths()

				// Si ya está visible, guardamos solo las prefs marcadas como “accepted”
			} else {
				localStorage.setItem('sbwscf-cookie-status', 'accepted')

				const allBoxes = document.querySelectorAll(
					'#sbwscf-cookie-categories input[type="checkbox"]:not(:disabled)'
				)
				const prefs = {}
				allBoxes.forEach((cb) => {
					const cat = cb.dataset.category
					if (cat) {
						prefs[cat] = cb.checked
					}
				})

				localStorage.setItem('sbwscf-cookie-preferences', JSON.stringify(prefs))
				savePreferences()
				injectScripts()

				categoriesBox.hidden = true
				minimizePanel()

                                preferencesBtn.textContent = preferencesDefaultLabel

				// Recalcular anchos para restaurar
				matchButtonWidths()
			}
		})
	}

	/*
	 * ------------------------------------------------------------------
	 * Inicializar estado del panel
	 * ------------------------------------------------------------------
	 */
	const hasStorage = localStorage.getItem('sbwscf-cookie-status')

        if (!hasStorage) {
                // Primera visita: mostrar panel con las opciones generales
                if (panel) panel.style.display = 'flex'
                if (container) container.style.display = 'none'

                if (categoriesBox) {
                        categoriesBox.hidden = true
                }

                if (preferencesBtn) preferencesBtn.textContent = preferencesDefaultLabel

                // Carga (vacía) preferencias y ajusta anchos
                loadPreferences()
                matchButtonWidths()
	} else {
		// Ya configurado: ocultar panel y mostrar manage consent
		if (categoriesBox) categoriesBox.hidden = true

		if (hasStorage === 'accepted') {
			loadPreferences()
			injectScripts()
		}

		matchButtonWidths()
		minimizePanel()
	}
	// ------------------------------------------------------------------
	// Igualar ancho de botones al más ancho
	// ------------------------------------------------------------------
	function matchButtonWidths() {
		updateActionButtons()
		const btns = panel.querySelectorAll('.sbwscf-smile-cookies-buttons button')
		if (!btns.length) {
			return
		}
		let maxW = 0
		// Resetear ancho para medir correctamente
		btns.forEach((btn) => {
			btn.style.width = 'auto'
		})
		// Calcular ancho máximo
		btns.forEach((btn) => {
			const w = Math.ceil(btn.getBoundingClientRect().width)
			if (w > maxW) {
				maxW = w
			}
		})
		// Fijar ancho máximo a todos
		btns.forEach((btn) => {
			btn.style.width = maxW + 'px'
		})
	}

	function updateActionButtons() {
		if (!acceptBtn && !denyBtn) {
			return
		}

		const preferencesOpen = !!categoriesBox && !categoriesBox.hidden

		if (acceptBtn) {
			acceptBtn.textContent = preferencesOpen ? acceptAllLabel : acceptInitialLabel
		}

		if (denyBtn) {
			denyBtn.textContent = preferencesOpen ? denyAllLabel : denyInitialLabel
		}
	}
})
