$(document).ready(function() {
    // Function to fetch and display students
    function fetchStudents() {
        $.ajax({
            url: 'getStudents.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let parent = $("#tablebody");
                parent.empty(); 

                data.forEach(student => {
                    let row = `<tr>
                        <td>${student['student_id']}</td>
                        <td>${student['profile_image'] ? `<img src='${student['profile_image']}' alt='Profile Image' width='50'>` : 'No Image'}</td>
                        <td>${student['first_name']}</td>
                        <td>${student['last_name']}</td>
                        <td>${student['email']}</td>
                        <td>${student['gender']}</td>
                        <td>${student['course'] || 'N/A'}</td>
                        <td>${student['user_address'] || 'N/A'}</td>
                        <td>${student['age'] || 'N/A'}</td>
                        <td>
                            <button class="btn btn-warning btn-sm btnEditStudent" data-id="${student['student_id']}">Edit</button>
                            <button class="btn btn-danger btn-sm btnDeleteStudent" data-id="${student['student_id']}">Delete</button>
                        </td>
                    </tr>`;
                    parent.append(row);
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching students:', error);
            }
        });
    }

    
    fetchStudents();

    
    $("#btnCreateStudent").click(function() {
        $("#addStudentModal").modal('show'); 
    });

    // Add Student
    $("#btnSubmitStudent").click(function() {
        let formData = new FormData($("#addStudentForm")[0]);

        $.ajax({
            url: "create_student.php", 
            type: "POST",
            dataType: "json",
            data: formData,
            processData: false, 
            contentType: false 
        }).done(function(result) {
            if (result.res === "success") {
                alert("Student added successfully!");
                fetchStudents(); 
                $("#addStudentModal").modal('hide'); 
                $("#addStudentForm")[0].reset(); 
            } else {
                alert("Error adding student: " + result.msg);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            alert("AJAX request failed: " + textStatus + ", " + errorThrown);
            console.error("Response Text: ", jqXHR.responseText); 
        });
    });

    // Edit Student
    $(document).on('click', '.btnEditStudent', function() {
        let studentId = $(this).data('id');

        $.ajax({
            url: "getStudents.php", 
            type: "GET",
            dataType: "json",
            data: { id: studentId }
        }).done(function(student) {
            $("#editStudentId").val(student.student_id);
            $("#editFirstName").val(student.first_name);
            $("#editLastName").val(student.last_name);
            $("#editEmail").val(student.email);
            $("#editGender").val(student.gender);
            $("#editCourse").val(student.course);
            $("#editAddress").val(student.user_address);
            $("#editBirthdate").val(student.birthdate);
            $("#editModal").modal('show'); 
        }).fail(function(jqXHR, textStatus, errorThrown) {
            alert("Error fetching student data: " + textStatus + ", " + errorThrown);
        });
    });

    $("#btnUpdateStudent").click(function() {
        let formData = new FormData($("#editStudentForm")[0]);

        $.ajax({
            url: "update_student.php", 
            type: "POST",
            dataType: "json",
            data: formData, 
            processData: false, 
            contentType: false 
        }).done(function(result) {
            if (result.res === "success") {
                alert("Student updated successfully!");
                fetchStudents(); 
                $("#editModal").modal('hide');
            } else {
                alert("Error updating student: " + result.msg);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            alert("AJAX request failed: " + textStatus + ", " + errorThrown);
        });
    });

    // Delete Student
    $(document).on('click', '.btnDeleteStudent', function() {
        let studentId = $(this).data('id');
        if (confirm("Are you sure you want to delete this student?")) {
            $.ajax({
                url: "delete_student.php",
                type: "POST",
                dataType: "json",
                data: { id: studentId }
            }).done(function(result) {
                if (result.res === "success") {
                    alert("Student deleted successfully!");
                    fetchStudents(); 
                } else {
                    alert("Error deleting student: " + result.msg);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX request failed: " + textStatus + ", " + errorThrown);
            });
        }
    });
    
});

// Fetch data from backend.php
function fetchData() {
    fetch('backend.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                window.location.href = 'login.php';
                return;
            }

            // Update profile section
            document.getElementById('profileImage').src = data.user.profile_image || 'default-profile.png';
            document.getElementById('profileName').textContent = `${data.user.first_name} ${data.user.last_name}`;
        })
        .catch(error => console.error('Error fetching data:', error));
}

// Real-time clock
function updateClock() {
    const now = new Date();
    const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    document.getElementById('realTimeClock').textContent = now.toLocaleDateString('en-US', options);
}

// Initialize the page
document.addEventListener('DOMContentLoaded', () => {
    fetchData(); 
    setInterval(updateClock, 1000);
    updateClock();
});

// Dark Mode Toggle
const darkModeToggle = document.getElementById('darkModeToggle');
const body = document.body;

// Load Dark Mode Preference
if (localStorage.getItem('darkMode') === 'enabled') {
    enableDarkMode();
    if (darkModeToggle) darkModeToggle.checked = true;
}

// Toggle Dark Mode
if (darkModeToggle) {
    darkModeToggle.addEventListener('change', () => {
        if (darkModeToggle.checked) {
            enableDarkMode();
            localStorage.setItem('darkMode', 'enabled');
        } else {
            disableDarkMode();
            localStorage.setItem('darkMode', 'disabled');
        }
    });
}

function enableDarkMode() {
    document.body.classList.add('dark-mode');
    document.querySelector('.sidebar').classList.add('dark-mode');
    document.querySelector('.content').classList.add('dark-mode');
    const realTimeClock = document.querySelector('.real-time-clock-container');
    if (realTimeClock) realTimeClock.classList.add('dark-mode');
    const infoBoxes = document.querySelectorAll('.info-box-container .system-health-box, .info-box-container .verified-users-box, .info-box-container .new-box');
    infoBoxes.forEach(box => box.classList.add('dark-mode'));
    const tableContainer = document.querySelector('.table-container');
    if (tableContainer) tableContainer.classList.add('dark-mode');
    const table = document.querySelector('.table');
    if (table) table.classList.add('dark-mode');
}

// Function to disable Dark Mode
function disableDarkMode() {
    document.body.classList.remove('dark-mode');
    document.querySelector('.sidebar').classList.remove('dark-mode');
    document.querySelector('.content').classList.remove('dark-mode');
    const realTimeClock = document.querySelector('.real-time-clock-container');
    if (realTimeClock) realTimeClock.classList.remove('dark-mode');
    const infoBoxes = document.querySelectorAll('.info-box-container .system-health-box, .info-box-container .verified-users-box, .info-box-container .new-box');
    infoBoxes.forEach(box => box.classList.remove('dark-mode'));
    const tableContainer = document.querySelector('.table-container');
    if (tableContainer) tableContainer.classList.remove('dark-mode');
    const table = document.querySelector('.table');
    if (table) table.classList.remove('dark-mode');
}

// Load Dark Mode Preference on Page Load
if (localStorage.getItem('darkMode') === 'enabled') {
    enableDarkMode();
    if (darkModeToggle) darkModeToggle.checked = true;
}

// Toggle Dark Mode on Switch Change
if (darkModeToggle) {
    darkModeToggle.addEventListener('change', () => {
        if (darkModeToggle.checked) {
            enableDarkMode();
            localStorage.setItem('darkMode', 'enabled');
        } else {
            disableDarkMode();
            localStorage.setItem('darkMode', 'disabled');
        }
    });
}

// Profile Modal Functionality
document.addEventListener('DOMContentLoaded', () => {
    // Fetch user data when the modal is opened
    $('#profileModal').on('show.bs.modal', function () {
        fetch('backend.php')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Populate the modal fields with user data
                const user = data.user;
                document.getElementById('modalProfileImage').src = user.profile_image || 'default-profile.png';
                document.getElementById('first_name').value = user.first_name || '';
                document.getElementById('last_name').value = user.last_name || '';
                document.getElementById('user_address').value = user.user_address || '';
                document.getElementById('birthdate').value = user.birthdate || '';

                // Apply dark mode to the modal if enabled
                if (localStorage.getItem('darkMode') === 'enabled') {
                    document.querySelector('.modal-content').classList.add('dark-mode');
                    document.querySelector('.modal-header').classList.add('dark-mode');
                    document.querySelector('.modal-body').classList.add('dark-mode');
                    document.querySelector('.modal-footer').classList.add('dark-mode');
                    document.querySelectorAll('.form-control').forEach(input => input.classList.add('dark-mode'));
                    document.querySelectorAll('label').forEach(label => label.classList.add('dark-mode'));
                    document.querySelector('.btn-primary').classList.add('btn-dark-mode');
                }
            })
            .catch(error => console.error('Error fetching user data:', error));
    });

    // Handle form submission for profile update
    document.getElementById('profileForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission behavior

        const formData = new FormData(this);

        fetch('update_profile.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Profile updated successfully!');

                    // Dynamically update the profile information on the page
                    const updatedUser = data.user;
                    document.getElementById('profileImage').src = updatedUser.profile_image || 'default-profile.png';
                    document.getElementById('profileName').textContent = `${updatedUser.first_name} ${updatedUser.last_name}`;

                    // Close the modal
                    $('#profileModal').modal('hide');
                } else {
                    alert(data.error || 'An error occurred while updating the profile.');
                }
            })
            .catch(error => console.error('Error updating profile:', error));
    });
});