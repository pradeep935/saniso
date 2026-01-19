document.addEventListener('DOMContentLoaded', function () {

    const combos = window.PRODUCT_COMBINATIONS || {};
    const allBtns = document.querySelectorAll('.variation-btn');
    const groups  = document.querySelectorAll('.variation-group');

    let selected = {};
    groups.forEach(g => {
        const set = g.dataset.set;
        selected[set] = null;
    });

    if (window.CURRENT_COLOR_ID) selected['color'] = String(window.CURRENT_COLOR_ID);
    if (window.CURRENT_SIZE_ID) selected['size']  = String(window.CURRENT_SIZE_ID);

    if (Object.values(selected).some(v => v === null)) {
        const firstKey = Object.keys(combos)[0];
        const ids = firstKey.split('_');
        let index = 0;
        groups.forEach(g => {
            const set = g.dataset.set;
            if (!selected[set]) selected[set] = ids[index] ?? null;
            index++;
        });
    }

    function refreshUI() {
        allBtns.forEach(btn => {
            const set = btn.dataset.set;
            const id  = btn.dataset.id;

            const testState = {...selected, [set]: id};
            const key = [...groups].map(g => testState[g.dataset.set]).filter(v => v != null).join('_');
            const exists = combos.hasOwnProperty(key);

            if (groups.length > 1) {
                if (set === 'color') {
                    btn.classList.remove('disabled');
                } else {
                    btn.classList.toggle('disabled', !exists);
                }
            } else {
                btn.classList.toggle('disabled', !exists);
            }

            btn.classList.toggle('valid', exists);
            btn.classList.toggle('invalid', !exists);

            if (selected[set] === id) {
                btn.classList.add('selected','active');
            } else {
                btn.classList.remove('selected','active');
            }
        });
    }

    refreshUI();

    allBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const set = btn.dataset.set;
            const id  = btn.dataset.id;

            if (btn.classList.contains('disabled')) return;

            if (set === 'color' && groups.length > 1) {
                selected[set] = id;
                refreshUI();
                return;
            }

            selected[set] = id;
            refreshUI();

            const key = [...groups].map(g => selected[g.dataset.set]).filter(v => v != null).join('_');
            if (combos[key] && window.location.href !== combos[key]) {
                window.location.href = combos[key];
            }
        });
    });

});
