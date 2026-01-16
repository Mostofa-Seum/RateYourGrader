function openAnalyticsModal(uniName) {
    const modal = document.getElementById('analyticsModal');
    const loading = document.getElementById('loading-spinner');
    const dataDiv = document.getElementById('analytics-data');
    
    // Set Header
    document.getElementById('modal-uni-name').textContent = uniName;
    
    // Reset State
    loading.style.display = 'block';
    dataDiv.style.display = 'none';
    modal.classList.add('active');

    const fd = new FormData();
    fd.append('action', 'get_uni_stats');
    fd.append('uni_name', uniName);

    fetch('UniversityAnalytics.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'success') {
            const data = d.data;
            
            // Populate Data
            document.getElementById('modal-faculty-count').textContent = data.faculty_count;
            document.getElementById('modal-rating').textContent = data.rating;

            // Animate Progress Bar
            let ratingVal = parseFloat(data.rating);
            if (isNaN(ratingVal)) ratingVal = 0;
            const percentage = (ratingVal / 5) * 100;
            document.getElementById('rating-progress').style.width = percentage + '%';

            // Show Data
            loading.style.display = 'none';
            dataDiv.style.display = 'block';
        } else {
            alert('Error fetching analytics.');
            closeAnalyticsModal();
        }
    })
    .catch(e => {
        console.error(e);
        alert('Server Error.');
        closeAnalyticsModal();
    });
}

function closeAnalyticsModal() {
    document.getElementById('analyticsModal').classList.remove('active');
    // Reset width for next animation
    document.getElementById('rating-progress').style.width = '0%';
}

// Close on outside click
window.onclick = function(e) {
    const modal = document.getElementById('analyticsModal');
    if (e.target === modal) {
        closeAnalyticsModal();
    }
}