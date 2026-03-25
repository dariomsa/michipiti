@if(!empty($autosaveEnabled) && !empty($autosaveUrl) && !empty($autosaveFields))
  @push('styles')
  <style>
    .autosave-indicator {
      position: fixed;
      top: 4.9rem;
      right: 1rem;
      z-index: 1080;
      min-width: 170px;
      max-width: 260px;
      padding: .55rem .75rem;
      border: 1px solid #e5e7eb;
      background: rgba(255, 255, 255, 0.96);
      color: #4b5563;
      font-size: .78rem;
      line-height: 1.2;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
      opacity: 0;
      pointer-events: none;
      transform: translateY(-6px);
      transition: opacity .18s ease, transform .18s ease;
    }

    .autosave-indicator.is-visible {
      opacity: 1;
      transform: translateY(0);
    }

    .autosave-indicator.is-saving {
      color: #1d4ed8;
      border-color: #bfdbfe;
    }

    .autosave-indicator.is-error {
      color: #b91c1c;
      border-color: #fecaca;
    }

    @media (max-width: 991.98px) {
      .autosave-indicator {
        top: 4.35rem;
        right: .75rem;
        left: .75rem;
        max-width: none;
      }
    }
  </style>
  @endpush

  <div id="autosaveIndicator" class="autosave-indicator" aria-live="polite"></div>

  @push('scripts')
  <script>
    (function () {
      const form = document.getElementById('formMain');
      const indicator = document.getElementById('autosaveIndicator');
      const autosaveUrl = @json($autosaveUrl);
      const fieldNames = @json(array_values($autosaveFields));
      const autosaveLaminas = @json(!empty($autosaveLaminas));
      const debounceMs = 10000;

      if (!form || !indicator || !autosaveUrl || !Array.isArray(fieldNames) || !fieldNames.length) {
        return;
      }

      let timer = null;
      let isSaving = false;
      let lastSnapshot = JSON.stringify(buildPayload());
      let lastSuccessText = '';

      function fieldByName(name) {
        return form.querySelector(`[name="${name}"]`);
      }

      function readFieldValue(name) {
        const fields = form.querySelectorAll(`[name="${name}"]`);
        if (!fields.length) return null;

        const first = fields[0];

        if (first.type === 'radio') {
          const checked = Array.from(fields).find((field) => field.checked);
          return checked ? checked.value : null;
        }

        if (first.type === 'checkbox') {
          return first.checked ? (first.value || '1') : '';
        }

        return first.value ?? '';
      }

      function buildPayload() {
        const payload = {};
        fieldNames.forEach((name) => {
          payload[name] = readFieldValue(name);
        });

        if (autosaveLaminas) {
          payload.laminas = Array.from(form.querySelectorAll('[data-lamina]')).map((box) => {
            const idField = box.querySelector('input[name$="[id]"]');
            const titleField = box.querySelector('input[name$="[titulo]"]');
            const descField = box.querySelector('textarea[name$="[descripcion]"]');

            return {
              id: idField ? idField.value : null,
              titulo: titleField ? (titleField.value ?? '') : '',
              descripcion: descField ? (descField.value ?? '') : '',
            };
          });
        }

        return payload;
      }

      function setIndicator(text, state) {
        indicator.textContent = text;
        indicator.classList.add('is-visible');
        indicator.classList.remove('is-saving', 'is-error');

        if (state === 'saving') indicator.classList.add('is-saving');
        if (state === 'error') indicator.classList.add('is-error');
      }

      function hideIndicatorLater() {
        window.clearTimeout(window.__autosaveHideTimer);
        window.__autosaveHideTimer = window.setTimeout(() => {
          if (!isSaving) {
            indicator.classList.remove('is-visible');
          }
        }, 2500);
      }

      async function saveNow() {
        const payload = buildPayload();
        const snapshot = JSON.stringify(payload);

        if (snapshot === lastSnapshot || isSaving) {
          return;
        }

        isSaving = true;
        setIndicator('Guardando...', 'saving');

        try {
          const response = await fetch(autosaveUrl, {
            method: 'PATCH',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(payload),
          });

          const data = await response.json().catch(() => ({}));

          if (!response.ok) {
            const message =
              data?.message ||
              (data?.errors && Object.values(data.errors).flat()[0]) ||
              'Error al guardar';
            throw new Error(message);
          }

          lastSnapshot = snapshot;
          lastSuccessText = 'Guardado automáticamente';
          setIndicator(lastSuccessText, 'success');
          hideIndicatorLater();
        } catch (error) {
          setIndicator(error?.message || 'Error al guardar', 'error');
        } finally {
          isSaving = false;
        }
      }

      function scheduleSave() {
        window.clearTimeout(timer);
        timer = window.setTimeout(saveNow, debounceMs);
      }

      function shouldWatchTarget(target) {
        if (!target || !(target instanceof Element)) {
          return false;
        }

        if (fieldNames.some((name) => target.matches(`[name="${name}"]`))) {
          return true;
        }

        if (autosaveLaminas && target.matches('input[name$="[titulo]"], textarea[name$="[descripcion]"]')) {
          return !!target.closest('[data-lamina]');
        }

        return false;
      }

      ['input', 'change'].forEach((eventName) => {
        form.addEventListener(eventName, (event) => {
          if (shouldWatchTarget(event.target)) {
            scheduleSave();
          }
        });
      });

      form.addEventListener('submit', () => {
        window.clearTimeout(timer);
      });
    })();
  </script>
  @endpush
@endif
