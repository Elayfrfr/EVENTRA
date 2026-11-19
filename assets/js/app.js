// Sign in page: change the heading when the Student / Faculty tab changes
var heading = document.getElementById('welcome');
if (heading) {
  document.querySelectorAll('input[name="role"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
      heading.textContent = 'Welcome ' + (radio.value === 'faculty' ? 'Faculty' : 'Student') + '!';
    });
  });
}
