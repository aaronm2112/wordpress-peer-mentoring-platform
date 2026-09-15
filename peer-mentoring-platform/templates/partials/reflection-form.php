<form class="peer-reflection-form" novalidate>
    <div class="peer-form-summary" role="alert" aria-live="assertive" tabindex="-1"></div>
    <div class="peer-form-field">
        <label for="peer-reflection-response">
            <?php esc_html_e('What is one takeaway from this activity?', 'peer-mentoring-platform-showcase'); ?>
        </label>
        <textarea id="peer-reflection-response" name="peer_reflection_response" rows="5" required aria-describedby="peer-reflection-help peer-reflection-error"></textarea>
        <p id="peer-reflection-help"><?php esc_html_e('Write a brief response before continuing.', 'peer-mentoring-platform-showcase'); ?></p>
        <p id="peer-reflection-error" class="peer-field-error" hidden></p>
    </div>
    <button type="submit"><?php esc_html_e('Save reflection', 'peer-mentoring-platform-showcase'); ?></button>
</form>