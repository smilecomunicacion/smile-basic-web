/* global wp, sbwscfCookieScripts */
/**
 * File: sbwscf-cookies-panel.js
 *
 * JavaScript para el panel de Cookies.
 *
 * @since 1.0.0
 */

document.addEventListener('DOMContentLoaded', function () {
	const CONSENT_KEY = 'sbwscf-cookie-consent'
	const LEGACY_STATUS_KEY = 'sbwscf-cookie-status'
	const LEGACY_PREFS_KEY = 'sbwscf-cookie-preferences'
	const hasCookieConfig = typeof sbwscfCookieScripts === 'object' && sbwscfCookieScripts !== null
	const cookieScripts =
		hasCookieConfig && Array.isArray(sbwscfCookieScripts.scripts)
			? sbwscfCookieScripts.scripts
			: []
	const currentConfigHash =
		hasCookieConfig && typeof sbwscfCookieScripts.configHash === 'string'
			? sbwscfCookieScripts.configHash
			: ''
	const defaultState = { status: 'unknown', prefs: {} }
	let consentState = { ...defaultState }

	function normalizePrefs(sourcePrefs) {
		const normalized = {}

		if (sourcePrefs && typeof sourcePrefs === 'object') {
			Object.keys(sourcePrefs).forEach((category) => {
				const value = sourcePrefs[category]

				if (value === true) {
					normalized[category] = true
				} else if (value === false) {
					normalized[category] = false
				} else if (typeof value === 'string') {
					const lowered = value.trim().toLowerCase()
					normalized[category] = lowered === 'true' || lowered === '1'
				}
			})
		}

		return normalized
	}

	function clearLegacyKeys() {
		try {
			localStorage.removeItem(LEGACY_STATUS_KEY)
			localStorage.removeItem(LEGACY_PREFS_KEY)
		} catch (e) {
			console.error('Unable to clear legacy consent keys', e)
		}
	}

	function removeConsentStorage() {
		try {
			localStorage.removeItem(CONSENT_KEY)
		} catch (e) {
			console.error('Unable to remove consent storage', e)
		}

		clearLegacyKeys()
	}

	function migrateLegacyConsent() {
		let status = 'unknown'
		let prefs = {}

		try {
			const legacyStatus = localStorage.getItem(LEGACY_STATUS_KEY)
			const legacyPrefs = localStorage.getItem(LEGACY_PREFS_KEY)

			if (legacyStatus) {
				status = legacyStatus
			}

			if (legacyPrefs) {
				try {
					const parsed = JSON.parse(legacyPrefs)
					if (parsed && typeof parsed === 'object') {
						prefs = parsed
					}
				} catch (err) {
					console.error('Invalid legacy preferences JSON', err)
				}
			}
		} catch (err) {
			console.error('Unable to read legacy consent', err)
		}

		clearLegacyKeys()

		if (status === 'unknown' && Object.keys(prefs).length === 0) {
			return null
		}

		return {
			status,
			prefs,
		}
	}

	function loadConsentState() {
		const fallbackState = { ...defaultState }
		let stored = null

		try {
			const raw = localStorage.getItem(CONSENT_KEY)
			if (raw) {
				stored = JSON.parse(raw)
			}
		} catch (e) {
			console.error('Invalid consent payload', e)
		}

		if (!stored || typeof stored !== 'object') {
			stored = migrateLegacyConsent()
		}

		if (!stored || typeof stored !== 'object') {
			return fallbackState
		}

		const revision = typeof stored.revision === 'string' ? stored.revision : ''
		const normalizedPrefs = normalizePrefs(stored.prefs)

		if (currentConfigHash && revision && revision !== currentConfigHash) {
			removeConsentStorage()
			return fallbackState
		}

		if (currentConfigHash && !revision && stored.status === 'accepted') {
			removeConsentStorage()
			return fallbackState
		}

		if (
			stored.status === 'accepted' &&
			cookieScripts.length > 0 &&
			Object.keys(normalizedPrefs).length === 0
		) {
			removeConsentStorage()
			return fallbackState
		}

		return {
			status: typeof stored.status === 'string' ? stored.status : 'unknown',
			prefs: normalizedPrefs,
		}
	}

	function persistConsent(status, prefs) {
		const normalizedPrefs = normalizePrefs(prefs)
		const payload = {
			status,
			prefs: normalizedPrefs,
		}

		if (currentConfigHash) {
			payload.revision = currentConfigHash
		}

		consentState = {
			status,
			prefs: normalizedPrefs,
		}

		try {
			localStorage.setItem(CONSENT_KEY, JSON.stringify(payload))
		} catch (e) {
			console.error('Unable to persist consent state', e)
		}

		clearLegacyKeys()
	}

	function storeConsent(status, prefs) {
		persistConsent(status, prefs)
	}

	consentState = loadConsentState()

	if (consentState.status !== 'unknown') {
		persistConsent(consentState.status, consentState.prefs)
	}

	const panel = document.getElementById('sbwscf-smile-cookies-panel')
	const manageBtn = document.getElementById('sbwscf-manage-consent-btn')
	const container = document.getElementById('sbwscf-manage-consent-container')
	const acceptBtn = panel?.querySelector('.sbwscf-smile-cookies-accept')
	const denyBtn = panel?.querySelector('.sbwscf-smile-cookies-deny')
	const preferencesBtn = panel?.querySelector('.sbwscf-smile-cookies-preferences')
	function getPreferenceInputs() {
		if (!panel) {
			return []
		}

		const nodeList = panel.querySelectorAll('input[data-category]')

		if (!nodeList || typeof nodeList.length !== 'number') {
			return []
		}

		return Array.from(nodeList)
	}
	const categoriesBox = document.getElementById('sbwscf-cookie-categories')

	const preferencesDefaultLabel =
		preferencesBtn?.dataset?.preferencesLabel || wp.i18n.__('Preferences', 'smile-basic-web')
	const preferencesAcceptLabel =
		preferencesBtn?.dataset?.acceptPreferencesLabel ||
		wp.i18n.__('Accept Preferences', 'smile-basic-web')
	const acceptInitialLabel =
		acceptBtn?.dataset?.initialLabel || wp.i18n.__('Accept', 'smile-basic-web')
	const acceptAllLabel =
		acceptBtn?.dataset?.allLabel || wp.i18n.__('Accept All', 'smile-basic-web')
	const denyInitialLabel = denyBtn?.dataset?.initialLabel || wp.i18n.__('Deny', 'smile-basic-web')
	const denyAllLabel = denyBtn?.dataset?.allLabel || wp.i18n.__('Deny All', 'smile-basic-web')

	function minimizePanel() {
		if (panel) panel.style.display = 'none'
		if (container) container.style.display = ''
	}

	function restorePanel() {
		const hasStatus = consentState.status !== 'unknown'

		if (panel) {
			panel.style.display = 'flex'
		}
		if (container) container.style.display = 'none'

		if (categoriesBox) {
			categoriesBox.hidden = !hasStatus
		}

		if (preferencesBtn) {
			preferencesBtn.textContent = hasStatus
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

	const activeObservers = new Map()

	function cleanupCategory(category) {
		if (typeof category !== 'string' || category === '') {
			return
		}

		const safeCategory = escapeSelector(category)

		if (activeObservers.has(category)) {
			const observer = activeObservers.get(category)
			observer.disconnect()
			activeObservers.delete(category)
		}

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
		const originalReplaceChild = Node.prototype.replaceChild
		const originalAppend = Node.prototype.append
		const originalPrepend = Node.prototype.prepend

		const markNode = (node) => {
			markManagedSubtree(category, node)
		}

		if (activeObservers.has(category)) {
			const previousObserver = activeObservers.get(category)
			previousObserver.disconnect()
			activeObservers.delete(category)
		}

		const observer =
			typeof MutationObserver === 'function'
				? new MutationObserver((mutations) => {
						mutations.forEach((mutation) => {
							if (!mutation.addedNodes) {
								return
							}

							mutation.addedNodes.forEach((node) => {
								markManagedSubtree(category, node)
							})
						})
				  })
				: null

		if (observer) {
			observer.observe(document.documentElement, {
				childList: true,
				subtree: true,
			})
			activeObservers.set(category, observer)
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

		Node.prototype.replaceChild = function (newChild, oldChild) {
			markNode(newChild)
			return originalReplaceChild.call(this, newChild, oldChild)
		}

		if (typeof originalAppend === 'function') {
			Node.prototype.append = function () {
				Array.prototype.forEach.call(arguments, (arg) => {
					if (arg instanceof Node) {
						markNode(arg)
					}
				})
				return originalAppend.apply(this, arguments)
			}
		}

		if (typeof originalPrepend === 'function') {
			Node.prototype.prepend = function () {
				Array.prototype.forEach.call(arguments, (arg) => {
					if (arg instanceof Node) {
						markNode(arg)
					}
				})
				return originalPrepend.apply(this, arguments)
			}
		}

		try {
			callback(markNode)
		} finally {
			document.createElement = originalCreateElement
			Node.prototype.appendChild = originalAppendChild
			Node.prototype.insertBefore = originalInsertBefore
			Node.prototype.replaceChild = originalReplaceChild

			if (typeof originalAppend === 'function') {
				Node.prototype.append = originalAppend
			}

			if (typeof originalPrepend === 'function') {
				Node.prototype.prepend = originalPrepend
			}
		}
	}

	function loadPreferences() {
		const prefs = consentState.prefs || {}
		const inputs = getPreferenceInputs()

		inputs.forEach((input) => {
			if (!input || typeof input.checked !== 'boolean') {
				return
			}
			const category = input.dataset ? input.dataset.category : undefined
			if (!category) {
				return
			}
			const state = prefs[category]
			if (typeof state === 'boolean') {
				input.checked = state
			}
		})
	}

	function injectScripts() {
		if (!cookieScripts.length) {
			return
		}

		cookieScripts.forEach((script) => {
			cleanupCategory(script.category)
		})

		if (consentState.status !== 'accepted') {
			return
		}

		cookieScripts.forEach((script) => {
			if (consentState.prefs[script.category] !== true || typeof script.code !== 'string') {
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
			const inputs = getPreferenceInputs()
			const activeBoxes = inputs.filter((input) => !input.disabled)

			activeBoxes.forEach((cb) => {
				cb.checked = true
			})

			const prefs = {}
			activeBoxes.forEach((cb) => {
				const cat = cb.dataset ? cb.dataset.category : undefined
				if (cat) prefs[cat] = cb.matches(':checked')
			})

			storeConsent('accepted', prefs)

			injectScripts()
			if (categoriesBox) categoriesBox.hidden = true
			minimizePanel()
		})
	}

	// --- Botón “Deny All” (antes “Deny”) ---
	if (denyBtn) {
		denyBtn.addEventListener('click', function () {
			const inputs = getPreferenceInputs()
			const activeBoxes = inputs.filter((input) => !input.disabled)
			const prefs = {}
			activeBoxes.forEach((cb) => {
				cb.checked = false
				const cat = cb.dataset ? cb.dataset.category : undefined
				if (cat) prefs[cat] = false
			})

			storeConsent('denied', prefs)

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
				const inputs = getPreferenceInputs()
				const activeBoxes = inputs.filter((input) => !input.disabled)
				const prefs = {}
				activeBoxes.forEach((cb) => {
					const cat = cb.dataset ? cb.dataset.category : undefined
					if (cat) {
						prefs[cat] = cb.matches(':checked')
					}
				})
				storeConsent('accepted', prefs)
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
	const hasConsent = consentState.status !== 'unknown'

	if (!hasConsent) {
		// Primera visita: mostrar panel con las opciones generales
		if (panel) panel.style.display = 'flex'
		if (container) container.style.display = 'none'

		if (categoriesBox) {
			categoriesBox.hidden = true
		}

		if (preferencesBtn) preferencesBtn.textContent = preferencesDefaultLabel

		matchButtonWidths()
	} else {
		// Ya configurado: ocultar panel y mostrar manage consent
		if (categoriesBox) categoriesBox.hidden = true

		loadPreferences()

		if (consentState.status === 'accepted') {
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
