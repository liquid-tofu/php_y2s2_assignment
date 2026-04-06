<section id="search-type">
  <label for="block"><?= ucfirst(strtolower("$col_block_name")); ?></label>
  <div class="div-btn">
    <select name="block" id="block">
      <option value="none" <?= ($search_block == 'none') ? 'selected' : '' ?>>None</option>
      <?php
      $columns = [];
      $list = $conn->query("SELECT DISTINCT $col_block FROM $tbl_block");
      if($list){
        $columns = $list->fetch_all(MYSQLI_ASSOC);
      }

      foreach($columns as $cell){
        $value = $cell[$col_block];
        $selected = (strcasecmp($search_block, $value) == 0) ? "selected" : "";
        echo '<option value="' . $value . '" ' . $selected . '>' . ucwords(strtolower($value)) . '</option>';
      }
      ?>
    </select>
  </div>
</section>
