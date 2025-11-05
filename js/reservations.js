document.addEventListener('DOMContentLoaded', function() {
    const timeSlots = [
        '11:00 AM', '11:30 AM', '12:00 PM', '12:30 PM', 
        '1:00 PM', '1:30 PM', '2:00 PM', '2:30 PM',
        '5:00 PM', '5:30 PM', '6:00 PM', '6:30 PM',
        '7:00 PM', '7:30 PM', '8:00 PM', '8:30 PM'
    ];

    const timeSlotsContainer = document.getElementById('timeSlots');
    let selectedTimeSlot = null;

    // Generate time slots
    timeSlots.forEach(time => {
        const slot = document.createElement('div');
        slot.className = 'timeslot';
        slot.textContent = time;
        
        // Randomly make some slots unavailable
        if (Math.random() < 0.3) {
            slot.classList.add('unavailable');
        } else {
            slot.addEventListener('click', () => selectTimeSlot(slot));
        }
        
        timeSlotsContainer.appendChild(slot);
    });

    function selectTimeSlot(slot) {
        if (slot.classList.contains('unavailable')) return;
        
        document.querySelectorAll('.timeslot').forEach(s => 
            s.classList.remove('selected'));
        slot.classList.add('selected');
        selectedTimeSlot = slot.textContent;
    }

    // Set minimum date to today
    const dateInput = document.getElementById('date');
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;

    // Handle form submission
    document.getElementById('reservationForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (!selectedTimeSlot) {
            alert('Please select a time slot');
            return;
        }

        const reservationData = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            date: document.getElementById('date').value,
            time: selectedTimeSlot,
            party: document.getElementById('party').value,
            special: document.getElementById('special').value
        };

        // Simulate sending reservation to server
        console.log('Reservation data:', reservationData);
        
        // Show confirmation modal
        const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        modal.show();

        // Simulate sending confirmation email
        sendConfirmationEmail(reservationData);
    });

    function sendConfirmationEmail(data) {
        // In a real application, this would make an API call to your server
        // to send an actual email. This is just a simulation.
        console.log('Sending confirmation email to:', data.email);
        
        const emailContent = `
            Dear ${data.name},

            Your reservation at Aling Nena Restaurant has been confirmed:

            Date: ${data.date}
            Time: ${data.time}
            Party Size: ${data.party}
            
            We look forward to serving you!

            Best regards,
            Aling Nena Restaurant
        `;

        console.log('Email content:', emailContent);
    }
});
