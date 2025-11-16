document.getElementById('login-form').addEventListener('submit', async function (e) {
  e.preventDefault();

  const form = this;
  const submitBtn = form.querySelector('button[type="submit"]');
  const studentId = form.student_id.value.trim();
  const password = form.password.value;

  if (!studentId || !password) {
    showMessage('Missing Information', 'Please enter your Student ID and Password.', 'error');
    return;
  }

  submitBtn.textContent = 'Signing in...';
  submitBtn.disabled = true;

  try {
    const response = await fetch('../BACKEND/login.php', {
      method: 'POST',
      body: new FormData(form)
    });

    const data = await response.json();

    if (data.success) {
      showMessage('Login Successful', 'Redirecting to dashboard...', 'success');
      setTimeout(() => window.location.href = data.redirect, 1000);
    } else {
      let title = 'Login Failed';
      if (data.status === 'pending') title = 'Account Pending Approval';
      if (data.status === 'rejected') title = 'Account Rejected';
      if (data.status === 'not_found') title = 'Account Not Found';
      if (data.status === 'password_error') title = 'Incorrect Password';

      const message = (data.error || 'Login failed.').replace(/\n/g, '<br>');
      showMessage(title, message, 'error');
      submitBtn.textContent = 'Sign In';
      submitBtn.disabled = false;
    }
  } catch (err) {
    console.error('Login error:', err);
    showMessage('Connection Error', 'Unable to reach the server. Please try again.', 'error');
    submitBtn.textContent = 'Sign In';
    submitBtn.disabled = false;
  }
});

function showMessage(title, message, type = 'error') {
  const popup = document.createElement('div');
  popup.className = `message-popup ${type}`;
  popup.innerHTML = `
    <div class="message-content">
      <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
      <div class="message-text">
        <h3>${title}</h3>
        <p>${message}</p>
      </div>
      <button class="close-btn" onclick="this.parentElement.parentElement.remove()">×</button>
    </div>
  `;
  document.body.appendChild(popup);
  setTimeout(() => popup.classList.add('show'), 50);

  if (type !== 'success') {
    setTimeout(() => popup.remove(), 5000);
  }
}
document.getElementById('login-form').addEventListener('submit', async function (e) {
	e.preventDefault();
  
	const form = this;
	const submitBtn = form.querySelector('button[type="submit"]');
	const studentId = form.student_id.value.trim();
	const password = form.password.value;
  
	if (!studentId || !password) {
	  showMessage('Missing Information', 'Please enter your Student ID and Password.', 'error');
	  return;
	}
  
	submitBtn.textContent = 'Signing in...';
	submitBtn.disabled = true;
  
	try {
	  const response = await fetch('../BACKEND/login.php', {
		method: 'POST',
		body: new FormData(form)
	  });
  
	  const data = await response.json();
  
	  if (data.success) {
		showMessage('Login Successful', 'Redirecting to dashboard...', 'success');
		setTimeout(() => window.location.href = data.redirect, 1000);
	  } else {
		let title = 'Login Failed';
		if (data.status === 'pending') title = 'Account Pending Approval';
		if (data.status === 'rejected') title = 'Account Rejected';
		if (data.status === 'not_found') title = 'Account Not Found';
		if (data.status === 'password_error') title = 'Incorrect Password';
  
		const message = (data.error || 'Login failed.').replace(/\n/g, '<br>');
		showMessage(title, message, 'error');
		submitBtn.textContent = 'Sign In';
		submitBtn.disabled = false;
	  }
	} catch (err) {
	  console.error('Login error:', err);
	  showMessage('Connection Error', 'Unable to reach the server. Please try again.', 'error');
	  submitBtn.textContent = 'Sign In';
	  submitBtn.disabled = false;
	}
  });
  
  function showMessage(title, message, type = 'error') {
	const popup = document.createElement('div');
	popup.className = `message-popup ${type}`;
	popup.innerHTML = `
	  <div class="message-content">
		<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
		<div class="message-text">
		  <h3>${title}</h3>
		  <p>${message}</p>
		</div>
		<button class="close-btn" onclick="this.parentElement.parentElement.remove()">×</button>
	  </div>
	`;
	document.body.appendChild(popup);
	setTimeout(() => popup.classList.add('show'), 50);
  
	if (type !== 'success') {
	  setTimeout(() => popup.remove(), 5000);
	}
  }
  