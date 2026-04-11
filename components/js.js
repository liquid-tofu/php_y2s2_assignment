// get rur filename
const curMainPath = window.location.pathname.split('/').pop();
const fields = {
  'search': document.getElementById('search-bar'),
  'clear' : document.getElementById('clear'),
  'block' : document.getElementById('block'),
  'from'  : document.querySelector('input[name="from"]'),
  'to'    : document.querySelector('input[name="to"]')
};

// update clear when page reload
function updateClearButton() {
  if (fields['clear'] && fields['search']) {
    fields['clear'].style.display = fields['search'].value ? 'block' : 'none';
  }
}
document.addEventListener('DOMContentLoaded', () => {
  updateClearButton();
});

// handle input event
function buildParams() {
  const params = new URLSearchParams();
  for (const [key, el] of Object.entries(fields)) {
    if (key !== 'clear' && el?.value && el.value !== '' && el.value !== 'none') {
      params.set(key, el.value);
    }
  }
  return params;
}

// add event for each type
fields['search']?.addEventListener('input', () => {
  if (fields['clear']) fields['clear'].style.display = fields['search'].value ? 'block' : 'none';
});
fields['search']?.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') { e.preventDefault(); window.location.href = curMainPath + '?' + buildParams(); }
});
fields['clear']?.addEventListener('click', () => {
  if (fields['search']) fields['search'].value = '';
  window.location.href = curMainPath + '?' + buildParams();
});

['block', 'from', 'to'].forEach(key => {
  fields[key]?.addEventListener('change', () => {
    window.location.href = curMainPath + '?' + buildParams();
  });
});

// some func for flexibility
function move_batch(batch, per_page) {
  // get link with param
  const params = new URLSearchParams(window.location.search);
  params.set('batch', batch);
  params.set('per_page', per_page);
  window.location.href = curMainPath + '?' + params.toString();
}

function sortColumn(displayName) {
  const map = colMap;
  const column  = map[displayName];
  const params  = new URLSearchParams(window.location.search);
  const curSort = params.get('sort_by')    || '';
  const curOrd  = params.get('sort_order') || '';

  let newSort = '', newOrder = '';
  if (curSort !== column)     { newSort = column; newOrder = 'DESC'; }
  else if (curOrd === 'DESC') { newSort = column; newOrder = 'ASC'; }

  if (newSort) {
    params.set('sort_by', newSort);
    params.set('sort_order', newOrder);
  } else {
    params.delete('sort_by');
    params.delete('sort_order');
  }
  params.delete('batch');
  params.delete('per_page');
  window.location.href = curMainPath + '?' + params.toString();
}



