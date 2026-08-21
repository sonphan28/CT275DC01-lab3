<?php

require_once __DIR__ . '/../src/bootstrap.php';

use CT275DC01_lab3\Contact;
use CT275DC01_lab3\Paginator;

$contact = new Contact($PDO);

$limit = (isset($_GET['limit']) && is_numeric($_GET['limit']))
  ? (int) $_GET['limit']
  : 5;

$page = (isset($_GET['page']) && is_numeric($_GET['page']))
  ? (int) $_GET['page']
  : 1;

$paginator = new Paginator(
  totalRecords: $contact->count(),
  recordsPerPage: $limit,
  currentPage: $page
);

$contacts = $contact->paginate(
  $paginator->recordOffset,
  $paginator->recordsPerPage
);

$pages = $paginator->getPages(length: 3);

include_once __DIR__ . '/../src/partials/header.php';

?>

<body>

  <?php include_once __DIR__ . '/../src/partials/navbar.php'; ?>

  <!-- Main Page Content -->
  <div class="container">

    <?php
    $subtitle = 'View all your contacts here.';
    include_once __DIR__ . '/../src/partials/heading.php';
    ?>

    <div class="row">
      <div class="col-12">

        <!-- Flash Message -->
        <?php if (isset($_SESSION['flash_message'])) : ?>

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

          <?php unset($_SESSION['flash_message']); ?>

        <?php endif; ?>


        <a
          href="/add.php"
          class="btn btn-primary mb-3">

          <i class="fa fa-plus"></i>
          New Contact

        </a>


        <!-- Table -->

        <table
          id="contacts"
          class="table table-striped table-bordered align-middle">

          <thead>

            <tr>

              <!-- Avatar -->
              <th
                scope="col"
                class="text-center"
                style="width: 90px;">

                Avatar

              </th>


              <!-- Name -->
              <th scope="col">
                Name
              </th>


              <!-- Phone -->
              <th scope="col">
                Phone
              </th>


              <!-- Date Created -->
              <th scope="col">
                Date Created
              </th>


              <!-- Notes -->
              <th scope="col">
                Notes
              </th>


              <!-- Actions -->
              <th
                scope="col"
                class="text-center"
                style="width: 170px;">

                Actions

              </th>

            </tr>

          </thead>


          <tbody>

            <?php foreach ($contacts as $contact_item) : ?>

              <tr>

                <!-- Avatar -->

                <td class="text-center">

                  <?php if (!empty($contact_item->avatar)) : ?>

                    <img
                      src="<?= html_escape($contact_item->avatar) ?>"
                      alt="Avatar"
                      class="rounded-circle"
                      style="
                        width: 60px;
                        height: 60px;
                        object-fit: cover;
                      ">

                  <?php else : ?>

                    <div
                      class="rounded-circle bg-secondary text-white d-inline-flex justify-content-center align-items-center"
                      style="
                        width: 60px;
                        height: 60px;
                      ">

                      <i class="fa fa-user"></i>

                    </div>

                  <?php endif; ?>

                </td>


                <!-- Name -->

                <td>

                  <?= html_escape($contact_item->name) ?>

                </td>


                <!-- Phone -->

                <td>

                  <?= html_escape($contact_item->phone) ?>

                </td>


                <!-- Date Created -->

                <td>

                  <?= html_escape(
                    date(
                      'd-m-Y',
                      strtotime($contact_item->created_at)
                    )
                  ) ?>

                </td>


                <!-- Notes -->

                <td>

                  <?= html_escape($contact_item->notes) ?>

                </td>


                <!-- Actions -->

                <td class="text-center text-nowrap">

                  <div
                    class="d-flex justify-content-center align-items-center gap-1">


                    <!-- Edit -->

                    <a
                      href="<?= '/edit.php?id=' . $contact_item->id ?>"
                      class="btn btn-xs btn-warning">

                      <i class="fa fa-pencil"></i>

                      Edit

                    </a>


                    <!-- Delete -->

                    <form
                      action="/delete.php"
                      method="post"
                      class="d-inline m-0">

                      <input
                        type="hidden"
                        name="id"
                        value="<?= $contact_item->id ?>">

                      <button
                        type="submit"
                        name="delete-contact"
                        class="btn btn-xs btn-danger">

                        <i class="fa fa-trash"></i>

                        Delete

                      </button>

                    </form>

                  </div>

                </td>

              </tr>

            <?php endforeach; ?>

          </tbody>

        </table>


        <!-- Pagination -->

        <nav class="d-flex justify-content-center">

          <ul class="pagination">


            <!-- Previous -->

            <li
              class="page-item<?= $paginator->getPrevPage()
                ? ''
                : ' disabled' ?>">

              <a
                role="button"
                href="/?page=<?= $paginator->getPrevPage() ?>&limit=<?= $limit ?>"
                class="page-link">

                <span>&laquo;</span>

              </a>

            </li>


            <!-- Page Numbers -->

            <?php foreach ($pages as $p) : ?>

              <li
                class="page-item<?= $paginator->currentPage === $p
                  ? ' active'
                  : '' ?>">

                <a
                  role="button"
                  href="/?page=<?= $p ?>&limit=<?= $limit ?>"
                  class="page-link">

                  <?= $p ?>

                </a>

              </li>

            <?php endforeach; ?>


            <!-- Next -->

            <li
              class="page-item<?= $paginator->getNextPage()
                ? ''
                : ' disabled' ?>">

              <a
                role="button"
                href="/?page=<?= $paginator->getNextPage() ?>&limit=<?= $limit ?>"
                class="page-link">

                <span>&raquo;</span>

              </a>

            </li>

          </ul>

        </nav>

      </div>
    </div>

  </div>


  <!-- Delete Confirm Modal -->

  <div
    id="delete-confirm"
    class="modal fade"
    tabindex="-1">

    <div class="modal-dialog">

      <div class="modal-content">

        <div class="modal-header">

          <h4 class="modal-title">
            Confirmation
          </h4>

          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal">
          </button>

        </div>

        <div class="modal-body">

          Do you want to delete this contact?

        </div>

        <div class="modal-footer">

          <button
            type="button"
            class="btn btn-danger"
            id="delete">

            Delete

          </button>

          <button
            type="button"
            data-bs-dismiss="modal"
            class="btn btn-secondary">

            Cancel

          </button>

        </div>

      </div>

    </div>

  </div>


  <?php include_once __DIR__ . '/../src/partials/footer.php'; ?>


  <!-- Delete Modal JS -->

  <script>

    const deleteButtons =
      document.querySelectorAll(
        'button[name="delete-contact"]'
      );


    deleteButtons.forEach(button => {

      button.addEventListener('click', function(e) {

        e.preventDefault();

        const form =
          button.closest('form');


        // Name bây giờ là cột thứ 2
        // vì Avatar là cột thứ 1.

        const nameTd =
          button
            .closest('tr')
            .querySelector('td:nth-child(2)');


        if (nameTd) {

          document.querySelector(
            '#delete-confirm .modal-body'
          ).textContent =
            `Do you want to delete "${nameTd.textContent.trim()}"?`;

        }


        const submitForm = function() {

          form.submit();

        };


        const deleteBtnModal =
          document.getElementById('delete');


        deleteBtnModal.addEventListener(
          'click',
          submitForm,
          {
            once: true
          }
        );


        const modalEl =
          document.getElementById('delete-confirm');


        modalEl.addEventListener(
          'hidden.bs.modal',
          function() {

            deleteBtnModal.removeEventListener(
              'click',
              submitForm
            );

          }
        );


        const confirmModal =
          new bootstrap.Modal(modalEl, {
            backdrop: 'static',
            keyboard: false
          });


        confirmModal.show();

      });

    });

  </script>

</body>

</html>