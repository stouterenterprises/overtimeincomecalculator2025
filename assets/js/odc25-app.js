(() => {
  const app = document.querySelector('.odc25-calculator');
  if (!app || typeof odc25Data === 'undefined') {
    return;
  }

  const maxBuckets = 10;
  const storageKey = 'odc25_draft_v1';

  const elements = {
    buckets: app.querySelector('.odc25-buckets'),
    addBucket: app.querySelector('[data-odc25-add-bucket]'),
    results: app.querySelector('.odc25-results'),
    methodInputs: app.querySelectorAll('input[name="odc25_method"]'),
    form: app.querySelector('form'),
    importForm: app.querySelector('.odc25-import-form'),
    importTextarea: app.querySelector('#odc25-import-text'),
    importFile: app.querySelector('#odc25-import-file'),
    importButton: app.querySelector('[data-odc25-import]'),
    templateButton: app.querySelector('[data-odc25-template]'),
    clearDraft: app.querySelector('[data-odc25-clear]'),
    exportButtons: app.querySelectorAll('[data-odc25-export]')
  };

  const createBucketRow = (bucket = {}) => {
    const row = document.createElement('div');
    row.className = 'odc25-bucket';
    row.innerHTML = `
      <div class="odc25-field">
        <label>Multiplier (m)</label>
        <input type="number" step="0.01" min="1" inputmode="decimal" name="multiplier" value="${bucket.multiplier ?? ''}" required>
      </div>
      <div class="odc25-field">
        <label>YTD OT Pay</label>
        <input type="number" step="0.01" min="0" inputmode="decimal" name="overtime_pay" value="${bucket.overtime_pay ?? ''}" required>
      </div>
      <div class="odc25-field">
        <label>YTD OT Hours (optional)</label>
        <input type="number" step="0.01" min="0" inputmode="decimal" name="hours" value="${bucket.hours ?? ''}">
      </div>
      <div class="odc25-field odc25-field-actions">
        <button type="button" class="button" data-odc25-remove>Remove</button>
      </div>
    `;

    row.querySelector('[data-odc25-remove]').addEventListener('click', () => {
      row.remove();
      refreshBuckets();
      saveDraft();
      calculate();
    });

    row.querySelectorAll('input').forEach((input) => {
      input.addEventListener('input', () => {
        saveDraft();
        calculate();
      });
    });

    return row;
  };

  const refreshBuckets = () => {
    const count = elements.buckets.querySelectorAll('.odc25-bucket').length;
    elements.addBucket.disabled = count >= maxBuckets;
    app.querySelector('.odc25-bucket-count').textContent = `${count}/${maxBuckets}`;
  };

  const getFormPayload = () => {
    const regularRate = parseFloat(app.querySelector('#odc25-regular-rate').value || '0');
    const filingStatus = app.querySelector('#odc25-filing-status').value;
    const magiValue = app.querySelector('#odc25-magi').value;
    const notes = app.querySelector('#odc25-notes').value;
    const method = app.querySelector('input[name="odc25_method"]:checked')?.value || 'flsa';

    const buckets = Array.from(elements.buckets.querySelectorAll('.odc25-bucket')).map((row) => {
      const multiplier = parseFloat(row.querySelector('input[name="multiplier"]').value || '0');
      const overtimePay = parseFloat(row.querySelector('input[name="overtime_pay"]').value || '0');
      const hoursValue = row.querySelector('input[name="hours"]').value;
      return {
        multiplier,
        overtime_pay: overtimePay,
        hours: hoursValue === '' ? null : parseFloat(hoursValue)
      };
    });

    return {
      regular_rate: regularRate,
      filing_status: filingStatus,
      magi: magiValue,
      notes,
      method,
      buckets
    };
  };

  const formatCurrency = (value) => {
    return `$${value.toFixed(2)}`;
  };

  const calculate = () => {
    const payload = getFormPayload();
    const regularRate = payload.regular_rate;
    const details = [];
    let totalPremium = 0;
    let totalOvertime = 0;

    payload.buckets.forEach((bucket, index) => {
      if (!bucket.multiplier || !bucket.overtime_pay) {
        return;
      }
      const hours = bucket.hours && bucket.hours > 0 ? bucket.hours : (regularRate > 0 ? bucket.overtime_pay / (regularRate * bucket.multiplier) : 0);
      const premium = regularRate * Math.max(0, bucket.multiplier - 1) * hours;
      totalPremium += premium;
      totalOvertime += bucket.overtime_pay;
      details.push({
        bucket: index + 1,
        multiplier: bucket.multiplier,
        overtime_pay: bucket.overtime_pay,
        hours,
        premium
      });
    });

    const method = payload.method;
    const eligible = method === 'full' ? totalOvertime : totalPremium;
    const cap = odc25Data.caps[payload.filing_status] || 0;
    const capped = cap ? Math.min(eligible, cap) : eligible;
    let phaseoutReduction = 0;
    const magi = payload.magi !== '' ? parseFloat(payload.magi) : null;
    const phaseout = odc25Data.phaseout[payload.filing_status];

    if (magi !== null && phaseout && phaseout.end > phaseout.start && magi > phaseout.start) {
      const excess = Math.min(magi, phaseout.end) - phaseout.start;
      const range = phaseout.end - phaseout.start;
      phaseoutReduction = range > 0 ? (excess / range) * capped : 0;
    }

    const deduction = Math.max(0, capped - phaseoutReduction);

    renderResults({
      details,
      totals: {
        premium_total: totalPremium,
        overtime_total: totalOvertime,
        eligible,
        cap,
        capped,
        phaseout_reduction: phaseoutReduction,
        deduction
      }
    });
  };

  const renderResults = (result) => {
    const totals = result.totals;
    const summary = app.querySelector('.odc25-summary');
    summary.innerHTML = `
      <div><strong>Estimated Deduction:</strong> ${formatCurrency(totals.deduction)}</div>
      <div><strong>Eligible Amount:</strong> ${formatCurrency(totals.eligible)}</div>
      <div><strong>Cap:</strong> ${formatCurrency(totals.cap)}</div>
      <div><strong>Phaseout Reduction:</strong> ${formatCurrency(totals.phaseout_reduction)}</div>
    `;

    const tbody = app.querySelector('.odc25-audit-body');
    tbody.innerHTML = '';
    result.details.forEach((bucket) => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${bucket.bucket}</td>
        <td>${bucket.multiplier.toFixed(2)}</td>
        <td>${formatCurrency(bucket.overtime_pay)}</td>
        <td>${bucket.hours.toFixed(2)}</td>
        <td>${formatCurrency(bucket.premium)}</td>
      `;
      tbody.appendChild(row);
    });

    elements.results.classList.remove('is-hidden');
  };

  const saveDraft = () => {
    const payload = getFormPayload();
    localStorage.setItem(storageKey, JSON.stringify(payload));
  };

  const loadDraft = () => {
    const raw = localStorage.getItem(storageKey);
    if (!raw) {
      return;
    }
    try {
      const payload = JSON.parse(raw);
      app.querySelector('#odc25-regular-rate').value = payload.regular_rate ?? '';
      app.querySelector('#odc25-filing-status').value = payload.filing_status ?? 'single';
      app.querySelector('#odc25-magi').value = payload.magi ?? '';
      app.querySelector('#odc25-notes').value = payload.notes ?? '';
      app.querySelector(`input[name="odc25_method"][value="${payload.method ?? 'flsa'}"]`).checked = true;
      elements.buckets.innerHTML = '';
      (payload.buckets || []).forEach((bucket) => {
        elements.buckets.appendChild(createBucketRow(bucket));
      });
      if (!payload.buckets || payload.buckets.length === 0) {
        elements.buckets.appendChild(createBucketRow());
      }
    } catch (error) {
      localStorage.removeItem(storageKey);
    }
  };

  const clearDraft = () => {
    localStorage.removeItem(storageKey);
  };

  const handleExport = async (format) => {
    const payload = getFormPayload();
    const formData = new FormData();
    formData.append('action', 'odc25_export');
    formData.append('nonce', odc25Data.export_nonce);
    formData.append('format', format);
    formData.append('payload', JSON.stringify(payload));

    const response = await fetch(odc25Data.ajax_url, {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      const error = await response.json();
      alert(error?.data?.message || 'Export failed.');
      return;
    }

    if (format === 'json') {
      const data = await response.json();
      const blob = new Blob([JSON.stringify(data.data, null, 2)], { type: 'application/json' });
      triggerDownload(blob, `odc25-packet-${Date.now()}.json`);
      return;
    }

    const blob = await response.blob();
    const extension = format === 'html' ? 'html' : format;
    triggerDownload(blob, `odc25-packet-${Date.now()}.${extension}`);
  };

  const triggerDownload = (blob, filename) => {
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
  };

  const handleImport = async () => {
    let csv = elements.importTextarea.value.trim();

    if (!csv && elements.importFile.files.length > 0) {
      csv = await elements.importFile.files[0].text();
    }

    if (!csv) {
      alert('Paste or upload a CSV first.');
      return;
    }

    const formData = new FormData();
    formData.append('action', 'odc25_import');
    formData.append('nonce', odc25Data.import_nonce);
    formData.append('csv', csv);

    const response = await fetch(odc25Data.ajax_url, {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      alert('Import failed.');
      return;
    }

    const data = await response.json();
    if (!data.success) {
      alert(data?.data?.message || 'Import failed.');
      return;
    }

    const payload = data.data.payload;
    app.querySelector('#odc25-regular-rate').value = payload.regular_rate ?? '';
    app.querySelector('#odc25-filing-status').value = payload.filing_status ?? 'single';
    app.querySelector('#odc25-magi').value = payload.magi ?? '';
    app.querySelector('#odc25-notes').value = payload.notes ?? '';
    app.querySelector(`input[name="odc25_method"][value="${payload.method ?? 'flsa'}"]`).checked = true;

    elements.buckets.innerHTML = '';
    (payload.buckets || []).forEach((bucket) => {
      elements.buckets.appendChild(createBucketRow(bucket));
    });

    refreshBuckets();
    saveDraft();
    calculate();
  };

  const downloadTemplate = () => {
    const template = [
      'META',
      'regular_rate,25',
      'filing_status,single',
      'method,flsa',
      'magi,',
      'notes,',
      '',
      'BUCKETS',
      'bucket,multiplier,overtime_pay,hours,premium',
      '1,1.5,2500,100,',
      '2,2.0,1200,,',
      '',
      'TOTALS',
      'premium_total,',
      'overtime_total,',
      'eligible,',
      'cap,',
      'capped,',
      'phaseout_reduction,',
      'deduction,'
    ].join('\n');
    const blob = new Blob([template], { type: 'text/csv' });
    triggerDownload(blob, 'odc25-template.csv');
  };

  const init = () => {
    loadDraft();

    if (elements.buckets.children.length === 0) {
      elements.buckets.appendChild(createBucketRow());
    }

    refreshBuckets();
    calculate();

    elements.addBucket.addEventListener('click', () => {
      if (elements.buckets.children.length >= maxBuckets) {
        return;
      }
      elements.buckets.appendChild(createBucketRow());
      refreshBuckets();
      saveDraft();
    });

    elements.form.querySelectorAll('input, select, textarea').forEach((input) => {
      input.addEventListener('input', () => {
        saveDraft();
        calculate();
      });
    });

    elements.exportButtons.forEach((button) => {
      button.addEventListener('click', () => handleExport(button.dataset.odc25Export));
    });

    elements.importButton.addEventListener('click', handleImport);
    elements.templateButton.addEventListener('click', downloadTemplate);
    elements.clearDraft.addEventListener('click', () => {
      clearDraft();
      window.location.reload();
    });

    elements.methodInputs.forEach((input) => {
      input.addEventListener('change', calculate);
    });
  };

  init();
})();
