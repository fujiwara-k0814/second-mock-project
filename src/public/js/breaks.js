//画面ロード時
document.addEventListener('DOMContentLoaded', () => {
    updateBreakRows();
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

//入力値満判定
function isRowFull(row) {
    const inputs = row.querySelectorAll('input[type="time"]'); //開始・終了を取得
    return Array.from(inputs).every(input => input.value); //両方満の場合'true'
}

//休憩項目の更新
function updateBreakRows() {
    const rows = document.querySelectorAll('.break-row');
    let firstEmptyIndex = null;

    //'break-row'の最初の空欄項目を記憶
    rows.forEach((row, index) => {
        if (firstEmptyIndex === null && isRowEmpty(row)) {
            firstEmptyIndex = index;
        }
    });

    //最初の空欄項目より後の空欄項目を削除
    if (firstEmptyIndex !== null) {
        rows.forEach((row, index) => {
            if (index > firstEmptyIndex) {
            row.remove();
            }
        });
    }

    //空欄がなければ1つ項目を追加
    const currentRows = document.querySelectorAll('.break-row');
    const lastRow = currentRows[currentRows.length - 1]; //indexに合わせる為'-1'

    if (!isRowEmpty(lastRow) || lastRow.querySelector('input').disabled) {
        const index = currentRows.length;
        const newRow = document.createElement('tr');
        const disableClass = statusCode === 'pending' ? 'disable' : '';
        const isDisabled = statusCode === 'pending' ? 'disabled' : '';
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

        newRow.classList.add('break-row');
        newRow.innerHTML = `
            <th>
                <label for="break[${index}]">休憩${index === 0 ? '' : index + 1}</label>
            </th>
            <td>
                <div class="time-wrapper">
                    <input type="time" name="break_start[${index}]" id="break[${index}]" 
                    class="detail-form__input ${disableClass}" ${isDisabled} value="${breakStartValue}">
                    <div class="detail-form__error">
                        ${breakStartError}
                    </div>
                </div>
                <span>～</span>
                <div class="time-wrapper">
                    <input type="time" name="break_end[${index}]" id="break[${index}]" 
                    class="detail-form__input ${disableClass}" ${isDisabled} value="${breakEndValue}">
                    <div class="detail-form__error">
                        ${breakEndError}
                    </div>
                </div>
            </td>
        `;
        
        //'備考'項目の前に挿入
        const remarkRow = document.querySelector('.comment-row'); 
        remarkRow.parentNode.insertBefore(newRow, remarkRow);
    }
}