<?php

require_once __DIR__ . '/../src/bootstrap.php';

use CT275DC01_lab3\Contact;

$contact = new Contact($PDO);

$id = isset($_REQUEST['id'])
  ? filter_var($_REQUEST['id'], FILTER_VALIDATE_INT)
  : false;

if (!$id || !($contact->find($id))) {
  redirect('/');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Xử lý avatar mới nếu người dùng chọn
  $avatarPath = $contact->handleUpload(
    $_FILES['avatar'] ?? null
  );

  $contactData = [
    'name'   => $_POST['name'] ?? '',
    'phone'  => $_POST['phone'] ?? '',
    'notes'  => $_POST['notes'] ?? '',
    'avatar' => $avatarPath,
  ];

  $errors = $contact->validate($contactData);

  if (empty($errors)) {

    $contact->fill($contactData);

    if ($contact->save()) {

      $_SESSION['flash_message'] =
        'Contact updated successfully!';

      redirect('/');
      exit;
    }
  }
}

include_once __DIR__ . '/../src/partials/header.php';

?>

<body>

  <?php include_once __DIR__ . '/../src/partials/navbar.php'; ?>

  <!-- Main Page Content -->
  <div class="container">

    <?php

    $subtitle = 'Update your contacts here.';
    include_once __DIR__ . '/../src/partials/heading.php';

    ?>

    <div class="row">
      <div class="col-12">

        <!-- Flash Message -->
        <?php if (isset($_SESSION['flash_message'])) : ?>

          <div class="col-md-6 offset-md-3">

            <div
              class="alert alert-success alert-dismissible fade show"
              role="alert">

              <?= html_escape($_SESSION['flash_message']) ?>

              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close">
              </button>

            </div>

          </div>

          <?php unset($_SESSION['flash_message']); ?>

        <?php endif; ?>


        <!--
          multipart/form-data bắt buộc để upload file.
          Form vẫn submit POST bình thường.
        -->
        <form
          method="post"
          enctype="multipart/form-data"
          class="col-md-6 offset-md-3">

          <input
            type="hidden"
            name="id"
            value="<?= $contact->id ?>">


          <!-- Name -->
          <div class="mb-3">

            <label
              for="name"
              class="form-label">

              Name

            </label>

            <input
              type="text"
              name="name"
              class="form-control<?= isset($errors['name'])
                ? ' is-invalid'
                : '' ?>"
              id="name"
              placeholder="Enter Name"
              value="<?= html_escape($contact->name) ?>">

            <?php if (isset($errors['name'])) : ?>

              <span class="invalid-feedback">

                <strong>
                  <?= $errors['name'] ?>
                </strong>

              </span>

            <?php endif ?>

          </div>


          <!-- Phone -->
          <div class="mb-3">

            <label
              for="phone"
              class="form-label">

              Phone Number

            </label>

            <input
              type="text"
              name="phone"
              class="form-control<?= isset($errors['phone'])
                ? ' is-invalid'
                : '' ?>"
              id="phone"
              placeholder="Enter Phone"
              value="<?= html_escape($contact->phone) ?>">

            <?php if (isset($errors['phone'])) : ?>

              <span class="invalid-feedback">

                <strong>
                  <?= $errors['phone'] ?>
                </strong>

              </span>

            <?php endif ?>

          </div>


          <!-- Avatar -->
          <div class="mb-3">

            <label
              for="avatar"
              class="form-label">

              Avatar

            </label>

            <input
              type="file"
              name="avatar"
              id="avatar"
              class="form-control"
              accept="image/jpeg,image/png,image/gif,image/webp">

            <!-- Current / Preview Avatar -->
            <div class="mt-3 text-center">

              <?php if (!empty($contact->avatar)) : ?>

                <img
                  id="avatar-preview"
                  src="<?= html_escape($contact->avatar) ?>"
                  class="img-thumbnail rounded-circle"
                  style="
                    width: 120px;
                    height: 120px;
                    object-fit: cover;
                  "
                  alt="Avatar">

              <?php else : ?>

                <img
                  id="avatar-preview"
                  src=""
                  class="img-thumbnail rounded-circle d-none"
                  style="
                    width: 120px;
                    height: 120px;
                    object-fit: cover;
                  "
                  alt="Avatar Preview">

              <?php endif; ?>

            </div>

          </div>


          <!-- Notes -->
          <div class="mb-3">

            <label
              for="notes"
              class="form-label">

              Notes

            </label>

            <textarea
              name="notes"
              id="notes"
              class="form-control<?= isset($errors['notes'])
                ? ' is-invalid'
                : '' ?>"
              placeholder="Enter notes (maximum character limit: 255)"><?= html_escape($contact->notes) ?></textarea>

            <?php if (isset($errors['notes'])) : ?>

              <span class="invalid-feedback">

                <strong>
                  <?= $errors['notes'] ?>
                </strong>

              </span>

            <?php endif ?>

          </div>


          <!-- Submit -->
          <button
            type="submit"
            name="submit"
            class="btn btn-primary">

            Update Contact

          </button>

        </form>

      </div>
    </div>

  </div>


  <?php include_once __DIR__ . '/../src/partials/footer.php'; ?>


  <!--
    JavaScript chỉ preview ảnh.
    Không dùng AJAX để submit.
  -->
  <script>

    const avatarInput =
      document.getElementById('avatar');

    const avatarPreview =
      document.getElementById('avatar-preview');


    avatarInput.addEventListener('change', function () {

      const file = this.files[0];

      if (!file) {
        return;
      }


      if (!file.type.startsWith('image/')) {

        return;
      }


      const reader = new FileReader();

      reader.onload = function (event) {

        avatarPreview.src =
          event.target.result;

        avatarPreview.classList.remove('d-none');
      };

      reader.readAsDataURL(file);

    });

  </script>

</body>

</html>