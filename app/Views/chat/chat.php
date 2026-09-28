<?= $this->extend('layout'); ?>
<?= $this->section('content'); ?>

<link rel="stylesheet" href="<?= base_url(env('ImagePath') . 'assets/css/chat.css') ?>">

<style>
    .capitalize-text {
        text-transform: capitalize;
    }
</style>

<div class="row clearfix">
    <div class="col-12">
        <div class="card">
            <div class="chat-app">
                <div class="card-body">
                    <div id="plist" class="people-list">
                        <div class="input-group" style="position: relative;">
                            <span class="input-group-text" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; z-index: 10;">
                                <i class="fa fa-search"></i>
                            </span>
                            <input type="text" id="searchUserInput" class="form-control" placeholder="Search users..." style="padding-left: 47px;">
                        </div>
                        <!-- <ul id="userList" class="user-list"></ul> -->

                        <div class="scrollable-container">
                            <ul class="list-unstyled chat-list mt-2 mb-0" id="userList"></ul>

                        </div>

                    </div>
                    <div class="chat">
                        <div class="chat-header clearfix">
                            <div class="row align-items-center">
                                <div class="col-lg-6 col-6 d-flex align-items-center">
                                    <a href="javascript:void(0);">
                                        <img id="chatUserImage" src="" alt="" class="user-avatar rounded-circle" height="40" width="40">
                                    </a>
                                    <div class="chat-about ms-2">
                                        <h6 class="m-b-0 capitalize-text" id="chatUserName"></h6>
                                        <div id="chatUserStatus" class="status-container" style="font-size: 10px;"></div>
                                    </div>
                                    <!-- status appears here -->

                                </div>
                                <div class="col-lg-6 col-6 text-end">
                                    <div class="date-time-box">
                                        <div id="currentDate" class="date"></div>
                                        <div id="currentTime" class="time"></div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="chat-history">
                            <ul class="m-b-0" id="chatMessages"></ul>
                        </div>
                        <div class="chat-message clearfix">
                            <form id="messageForm">
                                <div class="input-group">
                                    <!-- <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-send"></i></span>
                                </div> -->
                                    <input type="text" class="form-control" id="messageInput" placeholder="Enter text here...">
                                    <input type="hidden" id="receiverId">
                                    <label for="fileInput" class="btn btn-light" style="cursor: pointer;">
                                        <i class="fa fa-paperclip"></i>
                                    </label>
                                    <input type="file" id="fileInput" multiple style="display: none;">
                                    <button type="submit" class="btn" style="background-color:#E66136;color:white"><i class="fa fa-send" style="color:white"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>

<script>
    setInterval(function() {
        $.ajax({
            url: "<?= base_url('user/updateStatus') ?>",
            type: "POST",
            data: {
                user_id: <?= $_SESSION['user_id'] ?>
            },
            success: function(response) {
                console.log(response.message);
            }
        });
    }, 60000); // Run every 1 minute


    $(document).ready(function() {
        const token = localStorage.getItem('token');
        let receiver_id = '';

        $(document).ready(function() {
            fetchUsers(); // Load users on page load

            $("#searchUserInput").on("keyup", function() {
                let searchValue = $(this).val().toLowerCase();

                $(".user-item").each(function() {
                    let userName = $(this).find(".name").text().toLowerCase();

                    if (userName.includes(searchValue)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });

       
        function fetchUsers() {
            $.ajax({
                url: "<?= base_url('api/chat/getUsers') ?>",
                type: "GET",
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                dataType: "json",
                success: function(response) {
                    let userList = $("#userList");
                    userList.empty();

                    if (response.success && response.users.length > 0) {
                        let onlineUsers = [];
                        let offlineUsers = [];

                        // Split users by status
                        $.each(response.users, function(index, user) {
                            if (user.chat_status === 'online') {
                                onlineUsers.push(user);
                            } else {
                                offlineUsers.push(user);
                            }
                        });

                        // Helper to generate user HTML
                        const createUserHTML = (user) => {
                            let profileImage = user.profile_image ?
                                "<?= base_url('upload/') ?>" + user.profile_image :
                                "<?= base_url(env('ImagePath') . 'upload/1789966027_54c5a38ccda20f7c2bac.jpg') ?>";

                            let statusIcon = user.chat_status === 'online' ? '<i class="mdi mdi-circle text-success me-1"></i> Online' : '<i class="mdi mdi-circle text-danger me-1"></i> Offline';

                            return `
                        <li class="clearfix user-item" data-id="${user.id}" data-name="${user.name}" data-image="${profileImage}" data-status="${user.chat_status}">
                            <img src="${profileImage}" alt="avatar" class="user-avatar">
                            <div class="about">
                                <div class="name capitalize-text">${user.name}</div>
                               <div class="status-indicator">
                <span class="status-dot ${user.chat_status}"></span> ${user.chat_status.charAt(0).toUpperCase() + user.chat_status.slice(1)}
            </div>
                            </div>
                        </li>
                        <hr class="user-divider">
                    `;
                        };

                        // Append Online Users
                        if (onlineUsers.length > 0) {
                            userList.append('<li class="status-header"></li>');
                            $.each(onlineUsers, function(_, user) {
                                userList.append(createUserHTML(user));
                            });
                        }

                        // Append Offline Users
                        if (offlineUsers.length > 0) {
                            userList.append('<li class="status-header"></li>');
                            $.each(offlineUsers, function(_, user) {
                                userList.append(createUserHTML(user));
                            });
                        }

                        // Select first user
                        let allUsers = [...onlineUsers, ...offlineUsers];
                        let firstUser = allUsers[0];

                        if (firstUser) {
                            receiver_id = firstUser.id;
                            $("#receiverId").val(receiver_id);
                            $("#chatUserName").text(firstUser.name);
                            $("#chatUserStatus").text(firstUser.chat_status === 'online' ? 'Online' : 'Offline');
                            let firstUserImage = firstUser.profile_image ?
                                "<?= base_url('upload/') ?>" + firstUser.profile_image :
                                "<?= base_url(env('ImagePath') . 'upload/1789966027_54c5a38ccda20f7c2bac.jpg') ?>";
                            $("#chatUserImage").attr("src", firstUserImage);
                            $(".user-item").first().addClass("active");

                            fetchMessages();
                        }

                        // Handle user item clicks
                        $(".user-item").click(function() {
                            $(".user-item").removeClass("active");
                            $(this).addClass("active");

                            receiver_id = $(this).data("id");
                            let userName = $(this).data("name");
                            let userImage = $(this).data("image");
                            // let userStatus = $(this).find(".status-text").text();
                            let userStatus = $(this).data("status"); // <-- this line

                            $("#receiverId").val(receiver_id);
                            $("#chatUserName").text(userName);
                            let statusText = userStatus.charAt(0).toUpperCase() + userStatus.slice(1); // Capitalize first letter
                            $("#chatUserStatus").html(`<span class="status-dot ${userStatus}"></span> ${statusText}`);
                            // $("#chatUserStatus").text(userStatus.charAt(0).toUpperCase() + userStatus.slice(1)); // Capitalize
                            // $("#chatUserStatus").text(userStatus);
                            $("#chatUserImage").attr("src", userImage);

                            fetchMessages();
                        });
                    } else {
                        $("#chatUserName").text("Select a user");
                        $("#chatUserStatus").text("Status: -");
                        $("#chatUserImage").attr("src", "https://bootdey.com/img/Content/avatar/avatar2.png");
                    }
                }
            });
        }



        function fetchMessages() {
            if (receiver_id !== '') {
                $.ajax({
                    url: "<?= base_url('api/chat/getMessages/') ?>" + receiver_id,
                    type: "GET",
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    dataType: "json",
                    success: function(response) {
                        let chatMessages = $("#chatMessages");
                        chatMessages.empty();

                        let loggedInUserId = response.logged_in_user_id;

                        $.each(response.data, function(index, message) {
                            let isSentByMe = message.sender_id == loggedInUserId;
                            let messageClass = isSentByMe ? 'my-message float-right' : 'other-message float-left';

                            // Construct message body
                            let messageHtml = `
                        <li class="clearfix ${isSentByMe ? 'text-right' : 'text-left'}">
                            <div class="message-data ${isSentByMe ? 'float-right' : 'float-left'}">
                                <img src="${message.profile_image}" class="chat-avatar rounded-circle" height="40" width="40">
                                <span class="message-data-name"><strong>${isSentByMe ? 'You' : message.sender_name}</strong></span>
                                <span class="message-data-time">${message.sent_at}</span>
                            </div>
                        <div class="message ${messageClass}">`;

                            // Add text message
                            if (message.message) {
                                messageHtml += `<p>${message.message}</p>`;
                            }

                            // Add file attachments (images, PDFs, videos, etc.)
                            if (message.files && message.files.length > 0) {
                                messageHtml += `<div class="chat-files">`;
                                message.files.forEach(file => {
                                    let fileExtension = file.split('.').pop().toLowerCase();

                                    if (['png', 'jpg', 'jpeg', 'gif', 'webp'].includes(fileExtension)) {
                                        // Display images with preview modal
                                        let uniqueId = `imagePreview-${Math.random().toString(36).substr(2, 9)}`;
                                        messageHtml += `
                                                            <div class="chat-file-image">
                                                                <a href="#" data-toggle="modal" data-target="#${uniqueId}">
                                                                    <img src="${file}" class="chat-file-img preview-image" />
                                                                </a>
                                                                <div id="${uniqueId}" class="modal fade" tabindex="-1" role="dialog">
                                                                    <div class="modal-dialog modal-lg" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title">Image Preview</h5>
                                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body text-center">
                                                                                <img src="${file}" class="img-fluid" />
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>`;
                                    } else if (['mp4', 'webm', 'ogg'].includes(fileExtension)) {
                                        // Display video preview
                                        messageHtml += `
                                            <video controls class="chat-file-video">
                                                <source src="${file}" type="video/${fileExtension}">
                                                Your browser does not support the video tag.
                                            </video>`;
                                    } else if (fileExtension === 'pdf') {
                                        // Display PDF preview
                                        messageHtml += `
                                            <div class="chat-file-pdf">
                                                <embed src="${file}" type="application/pdf" class="pdf-preview" />
                                                <a href="${file}" target="_blank" class="chat-file-link">
                                                    <i class="fa fa-file-pdf"></i> View PDF
                                                </a>
                                            </div>`;
                                    } else if (['doc', 'docx'].includes(fileExtension)) {
                                        // DOC/DOCX Preview using Google Docs Viewer
                                        messageHtml += `
                                            <div class="chat-file-doc">
                                                <a href="${file}" target="_blank" class="chat-file-link">
                                                    <i class="fa fa-file-word"></i> View DOC/DOCX
                                                </a>
                                            </div>`;
                                    } else if (fileExtension === 'txt') {
                                        // Display PDF preview
                                        messageHtml += `
                                            <div class="chat-file-pdf">
                                                <embed src="${file}" type="application/pdf" class="pdf-preview" />
                                                <a href="${file}" target="_blank" class="chat-file-link">
                                                    <i class="fa fa-file-pdf"></i> View Text File
                                                </a>
                                            </div>`;
                                    } else {
                                        // Display other file types as a download link
                                        messageHtml += `
                                        <a href="${file}" target="_blank" class="chat-file-link">
                                            <i class="fa fa-file"></i> ${file.split('/').pop()}
                                        </a>`;
                                    }
                                });
                                messageHtml += `</div>`;
                            }

                            messageHtml += `</div></li>`;
                            chatMessages.append(messageHtml);
                        });

                        // Scroll to bottom after loading messages
                        $(".chat-history").scrollTop($(".chat-history")[0].scrollHeight);
                    }
                });
            }
        }
        setInterval(() => {
           fetchMessages(); // Auto refresh chat every 3 seconds
         }, 3000);
        $("#messageForm").submit(function(e) {
            e.preventDefault();

            let message = $("#messageInput").val().trim();
            let receiverId = $("#receiverId").val();
            let files = $("#fileInput")[0].files;

            // Frontend validation: Ensure message or file is provided
            if (!message && files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please enter a message or attach a file before sending.',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'hr-btnbg',

                    }
                });
                return;
            }

            let formData = new FormData();
            formData.append("message", message);
            formData.append("receiver_id", receiverId);

            for (let i = 0; i < files.length; i++) {
                formData.append("files[]", files[i]);
            }

            $.ajax({
                url: "<?= base_url('api/chat/sendMessage') ?>",
                type: "POST",
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function(response) {
                    if (response.status === "success") {
                        $("#messageInput").val("");
                        $("#fileInput").val(""); // Reset file input
                        fetchMessages();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'hr-btnbg',

                            }
                        });
                    }
                }
            });
        });

        $("#fileInput").on("change", function() {
            let fileNames = [];
            for (let i = 0; i < this.files.length; i++) {
                fileNames.push(this.files[i].name);
            }

            if (fileNames.length > 0) {
                $("#messageInput").val(fileNames.join(", ")); // Show file names in the input field
            } else {
                $("#messageInput").val(""); // Clear if no files selected
            }
        });

        fetchUsers();

        function updateDateTime() {
            const now = new Date();

            const day = now.toLocaleDateString('en-US', {
                weekday: 'long'
            }); // Full day name (Monday, Tuesday, etc.)
            const date = now.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }); // Apr 4, 2025
            const time = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            document.getElementById('currentDate').innerHTML = `<strong>${date}</strong> ${day}`;
            document.getElementById('currentTime').textContent = time;
        }

        // Update the time every second
        setInterval(updateDateTime, 1000);
        updateDateTime(); // Initial call


    });


   
</script>




<?= $this->endSection(); ?>
