//画面ロード時
document.addEventListener('DOMContentLoaded', () => {
    updateBreakRows();

    //old()により追加された'break-row'を復元
    const starts = window.oldBreakStarts ?? [];
    const ends = window.oldBreakEnds ?? [];
    const errors = window.laravelErrors ?? {};
    const existingRows = document.querySelectorAll('.break-row').length;
    const maxCount = Math.max(starts.length, ends.length);

    for (let i = existingRows; i < maxCount; i++) {
        const start = starts[i] ?? '';
        const end = ends[i] ?? '';
        const startError = errors[`break_start.${i}`]?.[0] ?? '';
        const endError = errors[`break_end.${i}`]?.[0] ?? '';
        addBreakRow(i, start, end, startError, endError);
    }
});

//'break-row'への入力イベントを監視
document.addEventListener('input', (e) => {
    const row = e.target.closest('.break-row');
    if (row && isRowFull(row) || isRowEmpty(row)) {
        updateBreakRows();
    }
});

//入力値空判定
function isRowEmpty(row) {
    const inputs = row.querySelectorAll('input[type="time"]'); //開始・終了を取得
    return Array.from(inputs).every(input => !input.value); //両方空の場合'true'
}

//入力値disabled判定
function isRowDisabled(row) {
    const inputs = row.querySelectorAll('input[type="time"]'); //開始・終了を取得
    return Array.from(inputs).every(input => input.disabled); //両方disabledの場合'true'
}

//入力値満判定
function isRowFull(row) {
    const inputs = row.querySelectorAll('input[type="time"]'); //開始・終了を取得
    return Array.from(inputs).every(input => input.value); //両方満の場合'true'
}

//'break-row'の更新
function updateBreakRows() {
    //空欄項目がなければ1つ項目を追加
    const currentRows = document.querySelectorAll('.break-row');
    const lastRow = currentRows[currentRows.length - 1]; //indexに合わせる為'-1'
    if (!isRowEmpty(lastRow) && !isRowDisabled(lastRow)) {
        const index = currentRows.length;
        
        const breakStartKey = `break_start.${index}`;
        const breakEndKey = `break_end.${index}`;
        const breakStartError = window.laravelErrors?.[breakStartKey]?.[0] ?? '';
        const breakEndError = window.laravelErrors?.[breakEndKey]?.[0] ?? '';
        const breakStartValue = window.oldBreakStarts?.[index]
            ?? window.breakDefaults?.[index]?.start
            ?? '';
        const breakEndValue = window.oldBreakEnds?.[index]
            ?? window.breakDefaults?.[index]?.end
            ?? '';

        addBreakRow(index, breakStartValue, breakEndValue, breakStartError, breakEndError);
    }

    //'break-row'の最初の空欄項目を記憶
    const rows = document.querySelectorAll('.break-row');
    let firstEmptyIndex = null;
    rows.forEach((row, index) => {
        if (firstEmptyIndex === null && isRowEmpty(row)) {
            firstEmptyIndex = index;
        }
    });
    
    //最初の空欄項目より後の空欄項目を削除
    if (firstEmptyIndex !== null) {
        rows.forEach((row, index) => {
            if ((index > firstEmptyIndex) || (index == firstEmptyIndex && isRowDisabled(row))) {
                row.remove();
            }
        });
    }
}

//'break-row'の追加
function addBreakRow(index, startValue = '', endValue = '', startError = '', endError = '') {
    const disableClass = statusCode === 'pending' ? 'disable' : '';
    const isDisabled = statusCode === 'pending' ? 'disabled' : '';
    const newRow = document.createElement('tr');
    newRow.classList.add('break-row');
    newRow.innerHTML = `
        <th>
            <label for="break_start_${index}">休憩${index === 0 ? '' : index + 1}</label>
        </th>
        <td>
            <div class="wrapper">
                <input type="time" name="break_start[${index}]" id="break_start_${index}"
                    class="detail-form__input ${disableClass}" ${isDisabled} value="${startValue}">
                <div class="detail-form__error">${startError}</div>
            </div>
            <span>～</span>
            <div class="wrapper">
                <input type="time" name="break_end[${index}]" id="break_end_${index}"
                    class="detail-form__input ${disableClass}" ${isDisabled} value="${endValue}">
                <div class="detail-form__error">${endError}</div>
            </div>
        </td>
    `;

    //'備考'項目の前に挿入
    const remarkRow = document.querySelector('.comment-row'); 
    remarkRow.parentNode.insertBefore(newRow, remarkRow);

    //追加された'input'に対して'.time-empty'を即時判定
    newRow.querySelectorAll('input[type="time"]').forEach(input => {
        input.classList.toggle('time-empty', !input.value);

        input.addEventListener('input', () => {
            input.classList.toggle('time-empty', !input.value);
        });
    });
}