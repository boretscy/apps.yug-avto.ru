// Регистрация Service Worker для PWA
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('sw.js')
      .then((reg) => {
        console.log('Service Worker успешно зарегистрирован:', reg.scope);
      })
      .catch((err) => {
        console.error('Ошибка регистрации Service Worker:', err);
      });
  });
}

const splashScreen = document.getElementById('splash-screen');
const errorScreen = document.getElementById('error-screen');
const retryButton = document.getElementById('retry-button');
const errorCodeEl = document.getElementById('error-code');

const errorCodes = [
  'ERR_CONNECTION_RESET',
  'ERR_CONNECTION_TIMED_OUT',
  'ERR_INTERNET_DISCONNECTED',
  'ERR_NAME_NOT_RESOLVED'
];

let attemptCount = 0;

function showErrorScreen() {
  // Выбираем код ошибки из списка по очереди
  const randomCode = errorCodes[attemptCount % errorCodes.length];
  errorCodeEl.textContent = randomCode;
  attemptCount++;

  // Переключаем экраны
  splashScreen.classList.remove('active');
  setTimeout(() => {
    errorScreen.classList.add('active');
  }, 100);
}

// Первоначальный запуск: имитируем загрузку 3 секунды
window.addEventListener('DOMContentLoaded', () => {
  setTimeout(showErrorScreen, 3000);
});

// Кнопка повтора
retryButton.addEventListener('click', () => {
  // Скрываем ошибку и показываем загрузку
  errorScreen.classList.remove('active');
  setTimeout(() => {
    splashScreen.classList.add('active');
    
    // Имитируем повторную попытку подключения в течение 2 секунд
    setTimeout(showErrorScreen, 2000);
  }, 300);
});
