// index.php
<?php
declare(strict_types=1);
require_once __DIR__ . '/src/LibraryFineCalculator.php';
use App\LibraryFineCalculator;
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$errors = [];
$result = null;
function old(string $key, $default = ''){ return $_POST[$key] ?? $default; }
function sanitizeInt(mixed $value, int $default = 0): int{ if (is_numeric($value)) { $v = (int)$value; return $v < 0 ? $default : $v; } return $default; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], (string)$token)) {
        $errors[] = 'Token inválido. Recarga la página.';
    }
    $booksRaw = $_POST['books'] ?? '';
    $daysRaw = $_POST['days'] ?? '';
    $sameDayRaw = $_POST['same_day'] ?? '';
    $books = sanitizeInt($booksRaw, -1);
    $days = sanitizeInt($daysRaw, -1);
    $sameDay = ($sameDayRaw === '1');
    if ($books <= 0) {
        $errors[] = 'La cantidad de libros debe ser mayor a cero.';
    }
    if ($days < 0) {
        $errors[] = 'Los días de retraso deben ser un entero no negativo.';
    }
    if (empty($errors)) {
        $calculator = new LibraryFineCalculator();
        $detail = $calculator->calculateDetailed($books, $days, $sameDay);
        $result = [
            'books' => $books,
            'days' => $days,
            'sameDay' => $sameDay,
            'base' => number_format($detail['base'], 2, '.', ','),
            'penalty' => number_format($detail['penalty'], 2, '.', ','),
            'before' => number_format($detail['totalBeforeDiscount'], 2, '.', ','),
            'after' => number_format($detail['totalAfterDiscount'], 2, '.', ','),
        ];
    }
}
include __DIR__ . '/templates/header.php';
?>
<form method="post" novalidate class="card">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
  <div class="form-grid">
    <div>
      <label for="books">Cantidad de libros atrasados</label>
      <input id="books" name="books" type="number" min="0" step="1" value="<?= htmlspecialchars((string) old('books', ''), ENT_QUOTES) ?>" required>
    </div>
    <div>
      <label for="days">Días de retraso</label>
      <input id="days" name="days" type="number" min="0" step="1" value="<?= htmlspecialchars((string) old('days', ''), ENT_QUOTES) ?>" required>
      <div class="small">Si todos los libros se devuelven juntos coloca los días correspondientes.</div>
    </div>
    <div class="checkbox-row full">
      <input type="checkbox" id="same_day" name="same_day" value="1" <?= old('same_day') === '1' ? 'checked' : '' ?>>
      <label for="same_day">Todos los libros devueltos el mismo día (20% descuento)</label>
    </div>
    <div class="controls full">
      <button type="submit">Calcular multa</button>
    </div>
  </div>
  <?php if (!empty($errors)): ?>
    <div class="error full" role="alert">
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e, ENT_QUOTES) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <?php if ($result !== null): ?>
    <div class="result full" aria-live="polite">
      <p><strong>Libros:</strong> <?= $result['books'] ?> · <strong>Días:</strong> <?= $result['days'] ?></p>
      <p><strong>Multa base:</strong> $<?= $result['base'] ?></p>
      <p><strong>Recargo por días:</strong> $<?= $result['penalty'] ?></p>
      <p><strong>Total sin descuento:</strong> $<?= $result['before'] ?></p>
      <p><strong>Total a pagar:</strong> $<?= $result['after'] ?> <?php if ($result['sameDay']) echo '<span class="small"> (20% aplicado)</span>'; ?></p>
    </div>
  <?php endif; ?>
</form>
<?php
include __DIR__ . '/templates/footer.php';
?>
