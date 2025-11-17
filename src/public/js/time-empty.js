//空欄判定時クラス付与
function updateTimeEmptyClasses() {
    document.querySelectorAll('input[type="time"]').forEach(input => {
        input.classList.toggle('time-empty', !input.value);
    });
}

//初回実行、イベント監視、定期実行
document.addEventListener('DOMContentLoaded', () => {
    //初回判定
    updateTimeEmptyClasses();

    //ユーザー入力時に即時反映
    document.querySelectorAll('input[type="time"]').forEach(input => {
        input.addEventListener('input', () => {
            input.classList.toggle('time-empty', !input.value);
        });
    });

    //定期的に再評価
    setInterval(updateTimeEmptyClasses, 60000);
});