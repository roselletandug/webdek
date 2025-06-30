document.addEventListener('DOMContentLoaded', function () {
    const themeSelect = document.getElementById('theme');
    const defaultOption = themeSelect.querySelector('option[value=""]');
    let themeAvailability = {};

    // Fetch theme availability from backend API
    fetch('../admin/get_theme_availability.php')
        .then(response => response.json())
        .then(data => {
            themeAvailability = {};
            data.forEach(item => {
                themeAvailability[item.theme_name] = item.is_available === "1" || item.is_available === 1;
            });

            // Update select options based on availability
            for (let i = 0; i < themeSelect.options.length; i++) {
                const option = themeSelect.options[i];
                if (option.value && themeAvailability.hasOwnProperty(option.value)) {
                    if (!themeAvailability[option.value]) {
                        option.disabled = true;
                    } else {
                        option.disabled = false;
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error fetching theme availability:', error);
        });

    // Change placeholder text color if selected theme is unavailable
    themeSelect.addEventListener('change', function () {
        const selectedValue = themeSelect.value;
        if (selectedValue === "") {
            defaultOption.style.color = '';
            return;
        }
        if (themeAvailability[selectedValue] === false) {
            // Show alert and reset selection
            alert('This theme is not available.');
            themeSelect.value = "";
            defaultOption.style.color = 'red';
        } else {
            defaultOption.style.color = '';
        }
    });

    // Initially set placeholder color to red if no theme selected
    if (themeSelect.value === "") {
        defaultOption.style.color = '';
    }
});
