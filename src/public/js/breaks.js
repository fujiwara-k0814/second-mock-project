//'break-row'への入力イベントを監視
document.addEventListener('input', (e) => {
    if (e.target.closest('.break-row')) {
        updateBreakRows();
    }
});

function updateBreakRows() {
    const rows = document.querySelectorAll('.break-row');
    let lastFilledIndex = -1;   //'-1'が未発見時の初期値

    //'break-row'の最終カウント値を記憶
    rows.forEach((row, index) => {
        if (!isRowEmpty(row)) {
        lastFilledIndex = index;
        }
    });

    //最後尾項目の前項目が空欄の場
    rows.forEach((row, index) => {
        if (index > lastFilledIndex) {
        row.remove();
        }
    });

    //空欄がなければ1つ追加
    const currentRows = document.querySelectorAll('.break-row');
    const lastRow = currentRows[currentRows.length - 1];
    if (!isRowEmpty(lastRow)) {
        const index = currentRows.length;
        const newRow = document.createElement('tr');
        newRow.classList.add('break-row');
        newRow.innerHTML = `
            <th>休憩${index === 0 ? '' : index + 1}</th>
            <td>
                <input type="time" name="break_start[${index}]">
                <span>～</span>
                <input type="time" name="break_end[${index}]">
            </td>
        `;
        lastRow.parentNode.appendChild(newRow);
    }
}

function isRowEmpty(row) {
    const inputs = row.querySelectorAll('input[type="time"]');  //開始・終了を取得
    return Array.from(inputs).every(input => !input.value); //両方空の場合'true'
}