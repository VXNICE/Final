document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('addRoomForm');
  const result = document.getElementById('resultMessage');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(form);

    try {
      const res = await fetch('api/add_room.php', {
        method: 'POST',
        body: formData
      });

      const data = await res.json();

      if (data.success) {
        result.textContent = '✅ Room added successfully!';
        result.className = 'text-green-600 mt-2';
        form.reset();
      } else {
        result.textContent = '❌ Error: ' + data.message;
        result.className = 'text-red-600 mt-2';
      }
    } catch (error) {
      result.textContent = '❌ Network error. Please try again.';
      result.className = 'text-red-600 mt-2';
      console.error('Error submitting form:', error);
    }
  });
});
