const originalValues = {};

function enableEdit() {
  const spans = document.querySelectorAll('#userInfoSection span');
  const inputs = document.querySelectorAll('#userInfoSection .edit-field');
  const saveBtn = document.querySelector('.save-btn');
  const cancelBtn = document.querySelector('.cancel-btn');
  const editBtn = document.querySelector('.edit-btn');

  spans.forEach((span, i) => {
    span.style.display = 'none';
    inputs[i].style.display = 'inline-block';
    originalValues[inputs[i].name] = inputs[i].value; // save original values
  });

  editBtn.style.display = 'none';
  saveBtn.style.display = 'inline-block';
  cancelBtn.style.display = 'inline-block';
}

function cancelEdit() {
  const spans = document.querySelectorAll('#userInfoSection span');
  const inputs = document.querySelectorAll('#userInfoSection .edit-field');
  const saveBtn = document.querySelector('.save-btn');
  const cancelBtn = document.querySelector('.cancel-btn');
  const editBtn = document.querySelector('.edit-btn');

  inputs.forEach((input, i) => {
    input.style.display = 'none';
    input.value = originalValues[input.name]; // restore original value
    spans[i].style.display = 'inline-block';
  });

  editBtn.style.display = 'inline-block';
  saveBtn.style.display = 'none';
  cancelBtn.style.display = 'none';
}
