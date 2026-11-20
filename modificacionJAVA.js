async function changeStep(delta) {
  const currentForm = document.getElementById(`step-${currentStep}`);
  
  // Validar campos obligatorios del paso actual
  if (delta === 1) {
    const inputs = currentForm.querySelectorAll('input[required]');
    let valid = true;
    inputs.forEach(input => {
      if (!input.checkValidity()) {
        input.reportValidity();
        valid = false;
      }
    });
    if (!valid) return;
  }

  currentStep += delta;

  // Si pasamos del último paso, enviamos los datos a PHP mediante AJAX/Fetch
  if (currentStep > totalSteps) {
    const formElement = document.getElementById('residenceForm');
    const formData = new FormData(formElement);

    try {
      const response = await fetch('guardar.php', {
        method: 'POST',
        body: formData
      });
      const result = await response.json();

      if (result.status === 'success') {
        document.getElementById('step-3').classList.remove('active');
        document.getElementById('step-success').classList.add('active');
        document.getElementById('nav-buttons').style.display = 'none';
        document.querySelector('.steps-bar').style.display = 'none';
      } else {
        alert("Hubo un error al guardar la solicitud: " + result.message);
        currentStep = totalSteps; // Mantenerse en el último paso si falla
      }
    } catch (error) {
      console.error("Error en la solicitud:", error);
      alert("No se pudo conectar con el servidor.");
      currentStep = totalSteps;
    }
  } else {
    showStep(currentStep);
  }
}