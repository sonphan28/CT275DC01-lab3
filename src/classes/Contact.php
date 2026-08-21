<?php

namespace CT275DC01_lab3;

use PDO;

class Contact
{
  private ?PDO $db;

  public int $id = -1;
  public $name;
  public $phone;
  public $notes;
  public $avatar;

  public $created_at;
  public $updated_at;

  public function __construct(?PDO $pdo)
  {
    $this->db = $pdo;
  }

  /**
   * Gán dữ liệu vào object Contact
   */
  public function fill(array $data): Contact
  {
    $this->name = $data['name'] ?? $this->name ?? '';
    $this->phone = $data['phone'] ?? $this->phone ?? '';
    $this->notes = $data['notes'] ?? $this->notes ?? '';

    if (array_key_exists('avatar', $data)) {
      $this->avatar = $data['avatar'];
    }

    return $this;
  }

  /**
   * Validate dữ liệu contact
   */
  public function validate(array $data): array
  {
    $errors = [];

    $name = trim($data['name'] ?? '');

    if (!$name) {
      $errors['name'] = 'Invalid name.';
    }

    $validPhone = preg_match(
      '/^(03|05|07|08|09|01[2|6|8|9])+([0-9]{8})\b$/',
      $data['phone'] ?? ''
    );

    if (!$validPhone) {
      $errors['phone'] = 'Invalid phone number.';
    }

    $notes = trim($data['notes'] ?? '');

    if (strlen($notes) > 255) {
      $errors['notes'] = 'Notes must be at most 255 characters.';
    }

    return $errors;
  }

  /**
   * Upload avatar
   *
   * File thật:
   *   public/uploads/
   *
   * Database lưu:
   *   /uploads/avatar_xxx.jpg
   */
  public function handleUpload(?array $file): ?string
  {
    // Không chọn file mới
    if (
      !$file ||
      !isset($file['error']) ||
      $file['error'] === UPLOAD_ERR_NO_FILE
    ) {
      return $this->avatar ?? null;
    }

    // Upload có lỗi
    if ($file['error'] !== UPLOAD_ERR_OK) {
      return $this->avatar ?? null;
    }

    // Kiểm tra file tạm
    if (
      !isset($file['tmp_name']) ||
      !is_uploaded_file($file['tmp_name'])
    ) {
      return $this->avatar ?? null;
    }

    // Kiểm tra MIME type
    $allowedMimeTypes = [
      'image/jpeg' => 'jpg',
      'image/png'  => 'png',
      'image/gif'  => 'gif',
      'image/webp' => 'webp'
    ];

    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!isset($allowedMimeTypes[$mimeType])) {
      return $this->avatar ?? null;
    }

    $extension = $allowedMimeTypes[$mimeType];

    /*
     * Contact.php nằm ở:
     *
     * project/src/classes/Contact.php
     *
     * public/uploads nằm ở:
     *
     * project/public/uploads/
     *
     * Vì vậy phải ../../
     */
    $uploadDir = __DIR__ . '/../../public/uploads/';

    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($uploadDir)) {
      if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        return $this->avatar ?? null;
      }
    }

    // Tạo tên file mới
    $newFilename = uniqid('avatar_', true) . '.' . $extension;

    $destination = $uploadDir . $newFilename;

    // Di chuyển file upload
    if (!move_uploaded_file(
      $file['tmp_name'],
      $destination
    )) {
      return $this->avatar ?? null;
    }

    // Xóa avatar cũ nếu có
    if (!empty($this->avatar)) {

      $oldAvatarPath = __DIR__ . '/../../public' . $this->avatar;

      if (
        file_exists($oldAvatarPath) &&
        is_file($oldAvatarPath)
      ) {
        @unlink($oldAvatarPath);
      }
    }

    // Lưu đường dẫn web vào database
    return '/uploads/' . $newFilename;
  }

  /**
   * Lấy tất cả contacts
   */
  public function all(): array
  {
    $contacts = [];

    $statement = $this->db->prepare(
      'select * from contacts'
    );

    $statement->execute();

    while ($row = $statement->fetch()) {
      $contact = new Contact($this->db);
      $contact->fillFromDbRow($row);
      $contacts[] = $contact;
    }

    return $contacts;
  }

  /**
   * Gán dữ liệu từ database vào object
   */
  protected function fillFromDbRow(array $row): Contact
  {
    $this->id = (int) $row['id'];
    $this->name = $row['name'];
    $this->phone = $row['phone'];
    $this->notes = $row['notes'];
    $this->avatar = $row['avatar'] ?? null;
    $this->created_at = $row['created_at'];
    $this->updated_at = $row['updated_at'];

    return $this;
  }

  /**
   * Đếm tổng số contacts
   */
  public function count(): int
  {
    $statement = $this->db->prepare(
      'select count(*) from contacts'
    );

    $statement->execute();

    return (int) $statement->fetchColumn();
  }

  /**
   * Phân trang contacts
   */
  public function paginate(
    int $offset = 0,
    int $limit = 10
  ): array {
    $contacts = [];

    $statement = $this->db->prepare(
      'select * from contacts
       limit :limit
       offset :offset'
    );

    $statement->bindValue(
      ':offset',
      $offset,
      PDO::PARAM_INT
    );

    $statement->bindValue(
      ':limit',
      $limit,
      PDO::PARAM_INT
    );

    $statement->execute();

    while ($row = $statement->fetch()) {
      $contact = new Contact($this->db);
      $contact->fillFromDbRow($row);
      $contacts[] = $contact;
    }

    return $contacts;
  }

  /**
   * Tìm contact theo ID
   */
  public function find(int $id): ?Contact
  {
    $statement = $this->db->prepare(
      'select * from contacts where id = :id'
    );

    $statement->execute([
      'id' => $id
    ]);

    if ($row = $statement->fetch()) {
      $this->fillFromDbRow($row);

      return $this;
    }

    return null;
  }

  /**
   * Update contact
   */
  public function update(array $data): bool
  {
    $this->fill($data);

    if ($this->id >= 0) {
      return $this->save();
    }

    return false;
  }

  /**
   * Lưu contact vào database
   */
  public function save(): bool
  {
    $result = false;

    // UPDATE
    if ($this->id >= 0) {

      $statement = $this->db->prepare(
        'update contacts
         set
           name = :name,
           phone = :phone,
           notes = :notes,
           avatar = :avatar,
           updated_at = now()
         where id = :id'
      );

      $result = $statement->execute([
        'name' => $this->name,
        'phone' => $this->phone,
        'notes' => $this->notes,
        'avatar' => $this->avatar,
        'id' => $this->id
      ]);
    }

    // INSERT
    else {

      $statement = $this->db->prepare(
        'insert into contacts
         (
           name,
           phone,
           notes,
           avatar,
           created_at,
           updated_at
         )
         values
         (
           :name,
           :phone,
           :notes,
           :avatar,
           now(),
           now()
         )'
      );

      $result = $statement->execute([
        'name' => $this->name,
        'phone' => $this->phone,
        'notes' => $this->notes,
        'avatar' => $this->avatar
      ]);

      if ($result) {
        $this->id = (int) $this->db->lastInsertId();
      }
    }

    return $result;
  }

  /**
   * Xóa contact và avatar
   */
  public function delete(): bool
  {
    // Xóa file avatar nếu có
    if (!empty($this->avatar)) {

      $avatarPath = __DIR__ . '/../../public' . $this->avatar;

      if (
        file_exists($avatarPath) &&
        is_file($avatarPath)
      ) {
        @unlink($avatarPath);
      }
    }

    // Xóa contact trong database
    $statement = $this->db->prepare(
      'delete from contacts where id = :id'
    );

    return $statement->execute([
      'id' => $this->id
    ]);
  }
}
