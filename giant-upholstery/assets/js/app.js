/* Giant Auto Upholstery - front-end behaviour (vanilla JS, no dependencies) */
(function () {
    'use strict';

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrf = csrfMeta ? csrfMeta.content : '';

    /* ------------------------------------------------------------
       Header: mobile menu + shadow on scroll
       ------------------------------------------------------------ */
    var header = document.getElementById('siteHeader');
    var navToggle = document.getElementById('navToggle');
    var mainNav = document.getElementById('mainNav');

    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function () {
            var open = mainNav.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }
    if (header) {
        var onScroll = function () {
            header.classList.toggle('is-scrolled', window.scrollY > 8);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ------------------------------------------------------------
       Home page: quote desk
       ------------------------------------------------------------ */
    var quoteSelect = document.getElementById('quoteService');
    if (quoteSelect) {
        var qPrice = document.getElementById('quotePrice');
        var qText = document.getElementById('quoteText');
        var qLink = document.getElementById('quoteLink');
        var updateQuote = function () {
            var opt = quoteSelect.options[quoteSelect.selectedIndex];
            qPrice.textContent = opt.getAttribute('data-price');
            qText.textContent = opt.getAttribute('data-text');
            qLink.href = 'booking.php?service=' + encodeURIComponent(opt.value);
        };
        quoteSelect.addEventListener('change', updateQuote);
        updateQuote();
    }

    /* ------------------------------------------------------------
       Form helpers
       ------------------------------------------------------------ */
    function todayString() {
        var d = new Date();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + m + '-' + day;
    }

    document.querySelectorAll('input[data-min-today]').forEach(function (el) {
        el.min = todayString();
    });

    function isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }
    function isSaPhone(v) { return /^(?:\+27|0)[6-8]\d{8}$/.test(v.replace(/[\s()\-]/g, '')); }

    function setError(form, name, message) {
        var out = form.querySelector('[data-error-for="' + name + '"]');
        if (out) out.textContent = message || '';
        var input = form.querySelector('[name="' + name + '"]');
        if (input) {
            var wrap = input.closest('.field') || input.closest('.check-field');
            if (wrap) wrap.classList.toggle('has-error', !!message);
            if (message) input.setAttribute('aria-invalid', 'true');
            else input.removeAttribute('aria-invalid');
        }
    }

    function showAlert(form, message, type) {
        var box = form.querySelector('.form-alert');
        if (!box) return;
        box.textContent = message;
        box.classList.toggle('is-error', type === 'error');
        box.classList.toggle('is-success', type === 'success');
        box.hidden = !message;
    }

    /* Returns an error message for a single control, or '' when valid. */
    function fieldMessage(form, el) {
        var type = el.type;
        var value = (el.value || '').trim();

        if (type === 'checkbox') {
            return el.required && !el.checked ? 'Please tick this box to continue.' : '';
        }
        if (type === 'radio') {
            var group = form.querySelectorAll('input[type="radio"][name="' + el.name + '"]');
            var any = Array.prototype.some.call(group, function (r) { return r.checked; });
            return any ? '' : 'Please choose a service.';
        }
        if (el.required && !value) return 'This field is required.';
        if (!value) return '';

        if (type === 'email' && !isEmail(value)) return 'Enter a valid email address.';
        if (el.getAttribute('data-validate') === 'sa-phone' && !isSaPhone(value)) {
            return 'Enter a valid South African mobile number, e.g. 071 234 5678.';
        }
        if (el.minLength > 0 && value.length < el.minLength) {
            return 'Use at least ' + el.minLength + ' characters.';
        }
        var match = el.getAttribute('data-match');
        if (match) {
            var other = form.querySelector('[name="' + match + '"]');
            if (other && other.value !== el.value) return 'Passwords do not match.';
        }
        if (el.hasAttribute('data-min-today') && type === 'date') {
            if (value < todayString()) return 'The date cannot be in the past.';
            var p = value.split('-');
            if (new Date(+p[0], +p[1] - 1, +p[2]).getDay() === 0) {
                return 'We are closed on Sundays. Please pick another day.';
            }
        }
        return '';
    }

    /* Validate every control inside `scope`. Returns the first invalid control or null. */
    function validate(form, scope) {
        var first = null;
        var seen = {};
        scope.querySelectorAll('input[name], select[name], textarea[name]').forEach(function (el) {
            if (el.disabled || el.classList.contains('hp') || el.type === 'hidden') return;
            if (seen[el.name]) return;
            seen[el.name] = true;
            var msg = fieldMessage(form, el);
            setError(form, el.name, msg);
            if (msg && !first) first = el;
        });
        return first;
    }

    /* Clear an error as soon as the person edits the field */
    document.addEventListener('input', function (ev) {
        var el = ev.target;
        var form = el.closest && el.closest('.js-form');
        if (form && el.name) {
            var wrap = el.closest('.field') || el.closest('.check-field');
            if (wrap && wrap.classList.contains('has-error')) setError(form, el.name, '');
        }
    });
    document.addEventListener('change', function (ev) {
        var el = ev.target;
        var form = el.closest && el.closest('.js-form');
        if (form && el.name) setError(form, el.name, '');
    });

    /* ------------------------------------------------------------
       Generic AJAX form handler: <form class="js-form" data-endpoint="api/x.php">
       ------------------------------------------------------------ */
    function initForm(form) {
        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            showAlert(form, '', '');

            var bad = validate(form, form);
            if (bad) {
                form.dispatchEvent(new CustomEvent('form:invalid', { detail: { element: bad } }));
                if (!form.classList.contains('wizard')) bad.focus();
                return;
            }

            var buttons = form.querySelectorAll('button[type="submit"]');
            var labels = [];
            buttons.forEach(function (b, i) { labels[i] = b.textContent; b.disabled = true; b.textContent = 'Sending...'; });

            fetch(form.getAttribute('data-endpoint'), {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-CSRF-Token': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
            .then(function (res) {
                return res.json().catch(function () { return { ok: false, message: 'Unexpected server response.' }; });
            })
            .then(function (data) {
                if (data.ok) {
                    var evt = new CustomEvent('form:success', { detail: data, cancelable: true });
                    var proceed = form.dispatchEvent(evt);
                    if (data.redirect) { window.location.href = data.redirect; return; }
                    if (proceed) {
                        showAlert(form, data.message || 'Saved.', 'success');
                        if (form.id === 'contactForm') form.reset();
                    }
                    return;
                }
                if (data.redirect) { window.location.href = data.redirect; return; }
                var names = [];
                if (data.errors) {
                    Object.keys(data.errors).forEach(function (n) { setError(form, n, data.errors[n]); names.push(n); });
                }
                form.dispatchEvent(new CustomEvent('form:servererror', { detail: { names: names } }));
                showAlert(form, data.message || 'Something went wrong. Please try again.', 'error');
            })
            .catch(function () {
                showAlert(form, 'We could not reach the server. Check your connection and try again.', 'error');
            })
            .then(function () {
                buttons.forEach(function (b, i) { b.disabled = false; b.textContent = labels[i]; });
            });
        });
    }
    document.querySelectorAll('.js-form').forEach(initForm);

    /* ------------------------------------------------------------
       Booking wizard
       ------------------------------------------------------------ */
    var wizardForm = document.getElementById('bookingForm');
    if (wizardForm) {
        var panels = Array.prototype.slice.call(wizardForm.querySelectorAll('.wizard-panel'));
        var stepperItems = document.querySelectorAll('#stepper li');
        var backBtn = wizardForm.querySelector('[data-wizard-back]');
        var nextBtn = wizardForm.querySelector('[data-wizard-next]');
        var submitBtn = wizardForm.querySelector('[data-wizard-submit]');
        var step = 0;
        var last = panels.length - 1;

        var val = function (name) {
            var el = wizardForm.querySelector('[name="' + name + '"]');
            return el ? el.value.trim() : '';
        };
        var prettyDate = function (iso) {
            if (!iso) return '';
            var p = iso.split('-');
            return new Date(+p[0], +p[1] - 1, +p[2]).toLocaleDateString('en-ZA', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
            });
        };
        var buildReview = function () {
            var checked = wizardForm.querySelector('input[name="service_id"]:checked');
            var rows = [
                ['Service', checked ? checked.getAttribute('data-name') : ''],
                ['Name', val('first_name') + ' ' + val('last_name')],
                ['Email', val('email')],
                ['Mobile', val('phone')],
                ['Address', val('address')],
                ['Date', prettyDate(val('preferred_date'))],
                ['Time', val('preferred_time')],
                ['Notes', val('damage_description') || 'None added']
            ];
            var list = document.getElementById('reviewList');
            list.innerHTML = '';
            rows.forEach(function (r) {
                var wrap = document.createElement('div');
                var dt = document.createElement('dt');
                var dd = document.createElement('dd');
                dt.textContent = r[0];
                dd.textContent = r[1];   // textContent: user input is never parsed as HTML
                wrap.appendChild(dt);
                wrap.appendChild(dd);
                list.appendChild(wrap);
            });
        };
        var show = function (i) {
            step = Math.max(0, Math.min(last, i));
            panels.forEach(function (p, idx) { p.classList.toggle('is-active', idx === step); });
            stepperItems.forEach(function (li, idx) {
                li.classList.toggle('is-active', idx === step);
                li.classList.toggle('is-done', idx < step);
            });
            backBtn.hidden = step === 0;
            nextBtn.hidden = step === last;
            submitBtn.hidden = step !== last;
            if (step === last) buildReview();
            showAlert(wizardForm, '', '');
            var card = wizardForm.closest('.form-card');
            if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };
        var panelOf = function (el) {
            var p = el.closest('.wizard-panel');
            return p ? panels.indexOf(p) : -1;
        };

        nextBtn.addEventListener('click', function () {
            var bad = validate(wizardForm, panels[step]);
            if (bad) { if (bad.focus) bad.focus({ preventScroll: false }); return; }
            show(step + 1);
        });
        backBtn.addEventListener('click', function () { show(step - 1); });

        wizardForm.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter' && ev.target.tagName !== 'TEXTAREA' && step < last) {
                ev.preventDefault();
                nextBtn.click();
            }
        });

        // Jump back to the step that holds an invalid / rejected field
        wizardForm.addEventListener('form:invalid', function (ev) {
            var i = panelOf(ev.detail.element);
            if (i > -1 && i !== step) show(i);
        });
        wizardForm.addEventListener('form:servererror', function (ev) {
            for (var n = 0; n < ev.detail.names.length; n++) {
                var el = wizardForm.querySelector('[name="' + ev.detail.names[n] + '"]');
                if (el) { var i = panelOf(el); if (i > -1) { show(i); break; } }
            }
        });

        wizardForm.addEventListener('form:success', function (ev) {
            ev.preventDefault();
            document.getElementById('bookingRef').textContent = ev.detail.reference;
            wizardForm.hidden = true;
            document.getElementById('stepper').hidden = true;
            var ok = document.getElementById('bookingSuccess');
            ok.hidden = false;
            ok.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        show(0);
    }

    /* ------------------------------------------------------------
       Dashboard: booking modal + mobile sidebar
       ------------------------------------------------------------ */
    var modal = document.getElementById('bookingModal');
    if (modal) {
        var fill = function (id, text) { document.getElementById(id).textContent = text; };
        var close = function () { modal.hidden = true; document.body.classList.remove('modal-open'); };

        document.querySelectorAll('.js-view-booking').forEach(function (btn) {
            btn.addEventListener('click', function () {
                fill('mRef', btn.dataset.ref);
                fill('mService', btn.dataset.service);
                fill('mDate', btn.dataset.date);
                fill('mTime', btn.dataset.time);
                fill('mStatus', btn.dataset.status);
                fill('mAddress', btn.dataset.address);
                fill('mNotes', btn.dataset.notes);
                modal.hidden = false;
                document.body.classList.add('modal-open');
                document.getElementById('modalClose').focus();
            });
        });
        document.getElementById('modalClose').addEventListener('click', close);
        modal.addEventListener('click', function (ev) { if (ev.target === modal) close(); });
        document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape' && !modal.hidden) close(); });
    }

    var dashToggle = document.getElementById('dashToggle');
    var dashSide = document.getElementById('dashSide');
    if (dashToggle && dashSide) {
        dashToggle.addEventListener('click', function () { dashSide.classList.toggle('open'); });
        dashSide.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () { dashSide.classList.remove('open'); });
        });
    }
})();
