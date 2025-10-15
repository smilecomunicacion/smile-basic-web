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
        const preferenceInputs = panel ? panel.querySelectorAll('input[data-category]') : []
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

        function escapeSelector(value) {
                if (typeof value !== 'string') {
                        return ''
                }

                if (window.CSS && typeof window.CSS.escape === 'function') {
                        return window.CSS.escape(value)
                }

                return value.replace(/[^a-zA-Z0-9_-]/g, '\\$&')
        }

        function markManagedSubtree(category, node) {
                if (!node) {
                        return
                }

                const queue = [node]

                while (queue.length) {
                        const current = queue.shift()
                        if (!current) {
                                continue
                        }

                        if (current.nodeType === 1) {
                                if (!current.hasAttribute('data-sbwscf-managed')) {
                                        current.setAttribute('data-sbwscf-managed', category)
                                }

                                if (current.childNodes && current.childNodes.length) {
                                        Array.prototype.forEach.call(current.childNodes, (child) => {
                                                queue.push(child)
                                        })
                                }
                        } else if (current.nodeType === 11 && current.childNodes && current.childNodes.length) {
                                Array.prototype.forEach.call(current.childNodes, (child) => {
                                        queue.push(child)
                                })
                        }
                }
        }

        function cleanupCategory(category) {
                if (typeof category !== 'string' || category === '') {
                        return
                }

                const safeCategory = escapeSelector(category)

                const managedSelector = '[data-sbwscf-managed="' + safeCategory + '"]'
                const scriptSelector = 'script[data-sbwscf-id="' + safeCategory + '"]'
                const fallbackSelector = '[data-sbwscf-fallback="' + safeCategory + '"]'

                document.querySelectorAll(scriptSelector).forEach((node) => {
                        node.remove()
                })

                document.querySelectorAll(fallbackSelector).forEach((node) => {
                        node.remove()
                })

                document.querySelectorAll(managedSelector).forEach((node) => {
                        node.remove()
                })
        }

        function runWithManagedNodes(category, callback) {
                if (typeof callback !== 'function') {
                        return
                }

                const originalCreateElement = document.createElement.bind(document)
                const originalAppendChild = Node.prototype.appendChild
                const originalInsertBefore = Node.prototype.insertBefore

                const markNode = (node) => {
                        markManagedSubtree(category, node)
                }

                document.createElement = function () {
                        const created = originalCreateElement.apply(document, arguments)
                        markNode(created)
                        return created
                }

                Node.prototype.appendChild = function (child) {
                        markNode(child)
                        return originalAppendChild.call(this, child)
                }

                Node.prototype.insertBefore = function (newNode, referenceNode) {
                        markNode(newNode)
                        return originalInsertBefore.call(this, newNode, referenceNode)
                }

                try {
                        callback(markNode)
                } finally {
                        document.createElement = originalCreateElement
                        Node.prototype.appendChild = originalAppendChild
                        Node.prototype.insertBefore = originalInsertBefore
                }
        }

        function savePreferences() {
                const prefs = {}
                preferenceInputs.forEach((input) => {
                        if (!input || typeof input.checked !== 'boolean') {
                                return
                        }
                        const category = input.dataset ? input.dataset.category : undefined
                        if (!category) {
                                return
                        }
                        prefs[category] = input.checked
                })
                localStorage.setItem('sbwscf-cookie-preferences', JSON.stringify(prefs))
        }

        function loadPreferences() {
                const saved = localStorage.getItem('sbwscf-cookie-preferences')
                if (!saved) return
                try {
                        const prefs = JSON.parse(saved)

                        if (!prefs || typeof prefs !== 'object') {
                                return
                        }

                        const normalized = {}

                        Object.keys(prefs).forEach((category) => {
                                const value = prefs[category]
                                let normalizedValue = false

                                if (value === true || value === false) {
                                        normalizedValue = value
                                } else if (typeof value === 'string') {
                                        const lowered = value.trim().toLowerCase()
                                        normalizedValue = lowered === 'true' || lowered === '1'
                                }

                                normalized[category] = normalizedValue
                        })

                        preferenceInputs.forEach((input) => {
                                if (!input || typeof input.checked !== 'boolean') {
                                        return
                                }
                                const category = input.dataset ? input.dataset.category : undefined
                                if (!category) {
                                        return
                                }
                                const state = normalized[category]
                                if (typeof state === 'boolean') {
                                        input.checked = state
                                }
                        })

                        localStorage.setItem('sbwscf-cookie-preferences', JSON.stringify(normalized))
                } catch (e) {
                        console.error('Invalid preferences JSON', e)
                }
        }

        function injectScripts() {
                const consent = localStorage.getItem('sbwscf-cookie-status')
                const saved = localStorage.getItem('sbwscf-cookie-preferences')

                if (typeof sbwscfCookieScripts !== 'undefined') {
                        sbwscfCookieScripts.scripts.forEach((script) => {
                                cleanupCategory(script.category)
                        })
                }

                if (consent !== 'accepted' || !saved || typeof sbwscfCookieScripts === 'undefined') {
                        return
                }

                try {
                        const prefs = JSON.parse(saved)
                        if (!prefs || typeof prefs !== 'object') {
                                return
                        }

                        sbwscfCookieScripts.scripts.forEach((script) => {
                                if (prefs[script.category] !== true || typeof script.code !== 'string') {
                                        return
                                }

                                const parts = splitScriptCode(script.code)

                                runWithManagedNodes(script.category, (markNode) => {
                                        if (parts.jsFragments.length) {
                                                const tag = document.createElement('script')
                                                tag.type = 'text/javascript'
                                                tag.setAttribute('data-sbwscf-id', script.category)
                                                tag.text = parts.jsFragments.join('\n')
                                                document.head.appendChild(tag)
                                        }

                                        if (parts.fallbackNodes.length && document.body) {
                                                const fallbackWrapper = document.createElement('div')
                                                fallbackWrapper.setAttribute('data-sbwscf-fallback', script.category)
                                                markNode(fallbackWrapper)
                                                parts.fallbackNodes.forEach((node) => {
                                                        markNode(node)
                                                        fallbackWrapper.appendChild(node)
                                                })
                                                document.body.appendChild(fallbackWrapper)
                                        }
                                })
                        })
                } catch (e) {
                        console.error('Error injecting scripts', e)
                }
        }

        function splitScriptCode(code) {
                const jsFragments = []
                const fallbackNodes = []

                if (typeof code !== 'string') {
                        return {
                                jsFragments,
                                fallbackNodes,
                        }
                }

                const template = document.createElement('template')
                template.innerHTML = code
                const nodes = template.content ? template.content.childNodes : []

                if (!nodes || !nodes.length) {
                        if (code.trim() !== '') {
                                jsFragments.push(code)
                        }

                        return {
                                jsFragments,
                                fallbackNodes,
                        }
                }

                Array.prototype.forEach.call(nodes, (node) => {
                        if (node.nodeType === 3) {
                                const text = node.textContent
                                if (!text) {
                                        return
                                }

                                const trimmed = text.trim()

                                if (trimmed === '') {
                                        return
                                }

                                if (trimmed.charAt(0) === '<') {
                                        fallbackNodes.push(document.createTextNode(text))
                                        return
                                }

                                jsFragments.push(text)
                        } else if (node.nodeType === 1) {
                                fallbackNodes.push(node.cloneNode(true))
                        }
                })

                if (!jsFragments.length && code.trim() !== '') {
                        jsFragments.push(code)
                }

                return {
                        jsFragments,
                        fallbackNodes,
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
                if (!panel) {
                        return
                }

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
