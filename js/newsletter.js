document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('newsletterForm');
    const emailInput = document.getElementById('newsletterEmail');
    const submitBtn = document.querySelector('.newsletter-btn'); // Using existing class

    if (!form || !emailInput || !submitBtn) {
        console.error('Newsletter elements not found');
        return;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const email = emailInput.value;

        if (!email) {
            alert("Please enter an email address.");
            return;
        }

        // Visual feedback - loading state
        const originalBtnContent = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        submitBtn.disabled = true;

        const formData = new FormData(form);
        if (!formData.has('email')) formData.append('email', email);

        fetch('subscribe.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Success message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Subscribed!',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(data.message);
                    }
                    form.reset();
                } else {
                    // Error message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message
                        });
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Something went wrong. Please try again.');
            })
            .finally(() => {
                submitBtn.innerHTML = originalBtnContent;
                submitBtn.disabled = false;
            });
    });
});
