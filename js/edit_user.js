$(document).ready(function () {
    function showLoading(buttonId, loadingId, show = true) {
        $(buttonId).prop("disabled", show);
        $(loadingId).toggleClass("d-none", !show);
    }

    function showError(errorId, message) {
        $(errorId).removeClass("d-none").text(message);
    }

    function openEditUserModal(userData) {
        $("#editUserId").val(userData.id);
        $("#editUsername").val(userData.username);
        $("#editEmail").val(userData.email);
        $("#editPoints").val(userData.points);
        $("#editTrustPoints").val(userData.trust_points);

        // Đảm bảo giá trị role hợp lệ
        const validRoles = ["admin", "user", "moderator"];
        if (validRoles.includes(userData.role.toLowerCase())) {
            $("#editRole").val(userData.role.toLowerCase());
        } else {
            $("#editRole").val("user"); 
        }

        $("#editFullname").val(userData.fullname);
        $("#editGender").val(userData.gender);
        $("#currentAvatarPreview").attr("src", userData.avatar ? userData.avatar : "default-avatar.png");

        $("#editUserModal").modal("show");
    }

    window.openEditUserModal = openEditUserModal;

    $("#editUserForm").submit(async function (e) {
        e.preventDefault();
        showLoading("#editUserForm button[type=submit]", "#editUserLoading", true);
        $("#editUserError").addClass("d-none");

        const formData = new FormData(this);

        try {
            const response = await $.ajax({
                url: "edit_user.php",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
            });

            if (response.trim() === "success") {
                alert("Cập nhật người dùng thành công!");
                $("#editUserModal").modal("hide");
                location.reload();
            } else {
                showError("#editUserError", "Có lỗi xảy ra: " + response);
            }
        } catch (error) {
            showError("#editUserError", "Lỗi kết nối: " + error);
        } finally {
            showLoading("#editUserForm button[type=submit]", "#editUserLoading", false);
        }
    });

    $("#editAvatar").change(function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => $("#currentAvatarPreview").attr("src", e.target.result);
            reader.readAsDataURL(file);
        }
    });
});
