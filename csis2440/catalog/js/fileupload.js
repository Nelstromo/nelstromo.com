
    const input = document.getElementById('profilePic');
    const nameEl = document.getElementById('profilePicName');
    const preview = document.getElementById('profilePreview');

    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if (!file) {
        nameEl.textContent = 'No file chosen';
        preview.hidden = true;
        preview.removeAttribute('src');
        return;
      }

      nameEl.textContent = file.name;

      if (file.type.startsWith('image/')) {
        const url = URL.createObjectURL(file);
        preview.src = url;
        preview.hidden = false;
        preview.onload = () => URL.revokeObjectURL(url);
      } else {
        preview.hidden = true;
        preview.removeAttribute('src');
      }
    });

