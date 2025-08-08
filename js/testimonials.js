// Real-time search functionality
document.getElementById('searchTestimonials').addEventListener('keyup', function(e) {
    const searchText = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
    });
});

// Status toggle functionality
document.querySelectorAll('.status-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        fetch('update_testimonial.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating status');
        });
    });
});

// Delete confirmation
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this testimonial? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="delete_testimonial" value="1">
            <input type="hidden" name="testimonial_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}