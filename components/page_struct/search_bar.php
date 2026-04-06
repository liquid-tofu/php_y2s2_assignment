<section id="search">
  <input type="text" name="search" id="search-bar"
          placeholder="Search by name or id"
          value="<?= htmlspecialchars($search) ?>"
          autocomplete="off"
          onkeypress="handleEnter(event)">
  <button type="button" id="clear">Clear</button>
  <button type="submit" name="submit" id="submit">
    <svg width="34" height="34" viewBox="0 0 34 34" fill="none">
      <path d="M16 24c4.4183 0 8-3.5817 8-8 0-4.4183-3.5817-8-8-8-4.4183 0-8 3.5817-8 8 0 4.4183 3.5817 8 8 8z"
            stroke="#e2e2e2ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M26.0001 26.0004l-4.35-4.35"
            stroke="#e2e2e2ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </button>
</section>
