document.addEventListener('DOMContentLoaded', function () {

    const combos = window.PRODUCT_COMBINATIONS || {};
    const CURRENT_COLOR_ID = window.CURRENT_COLOR_ID;
    const CURRENT_SIZE_ID  = window.CURRENT_SIZE_ID;

    let selected = { color: null, size: null };

    const colorBtns = document.querySelectorAll('.color-btn');
    const sizeBtns  = document.querySelectorAll('.size-btn');

    function buildKey(colorId, sizeId) {
        return String(colorId) + '_' + String(sizeId);
    }

    function filterSizesByColor(colorId) {
        sizeBtns.forEach(btn => {
            const key = buildKey(colorId, btn.dataset.id);
            if (combos.hasOwnProperty(key)) {
                btn.classList.add('valid');
                btn.classList.remove('invalid', 'selected', 'disabled');
            } else {
                btn.classList.add('invalid', 'disabled');
                btn.classList.remove('valid', 'selected');
            }
        });
    }

    function autoSelectSize(colorId) {
        let selectedSize = null;

        if (CURRENT_SIZE_ID && combos.hasOwnProperty(buildKey(colorId, CURRENT_SIZE_ID))) {
            selectedSize = CURRENT_SIZE_ID;
        } else {
            for (let btn of sizeBtns) {
                const key = buildKey(colorId, btn.dataset.id);
                if (combos.hasOwnProperty(key)) {
                    selectedSize = btn.dataset.id;
                    break;
                }
            }
        }

        if (selectedSize) {
            selected.size = selectedSize;
            sizeBtns.forEach(btn => btn.classList.remove('selected'));
            const sizeBtn = document.querySelector('.size-btn[data-id="' + selectedSize + '"]');
            if (sizeBtn) sizeBtn.classList.add('selected');
        }
    }

    /* ===== INIT ===== */
    colorBtns.forEach(b => b.classList.remove('active'));
    sizeBtns.forEach(b => b.classList.remove('active', 'disabled', 'valid', 'invalid', 'selected'));

    if (CURRENT_COLOR_ID) {
        selected.color = CURRENT_COLOR_ID;
        const colorBtn = document.querySelector('.color-btn[data-id="' + CURRENT_COLOR_ID + '"]');
        if (colorBtn) colorBtn.classList.add('active');

        filterSizesByColor(selected.color);
        autoSelectSize(selected.color);
    }

    /* ===== COLOR CLICK ===== */
    colorBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            selected.color = this.dataset.id;
            selected.size = null;

            colorBtns.forEach(b => b.classList.remove('active'));
            sizeBtns.forEach(b => b.classList.remove('active', 'selected'));

            this.classList.add('active');

            filterSizesByColor(selected.color);
            autoSelectSize(selected.color);
        });
    });

    /* ===== SIZE CLICK ===== */
    sizeBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            if (btn.classList.contains('disabled')) return;

            selected.size = btn.dataset.id;
            sizeBtns.forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');

            const key = buildKey(selected.color, selected.size);
            if (combos.hasOwnProperty(key) && window.location.href !== combos[key]) {
                window.location.href = combos[key];
            }
        });
    });

});