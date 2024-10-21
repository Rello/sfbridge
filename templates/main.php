<?php
/**
 * Salesforce Bridge
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use OCP\Util;
Util::addScript('sfbridge', 'app');
Util::addScript('sfbridge', 'userGuidance');
?>

<div id="app-navigation" class="hidden">
</div>

<div id="app-content">
    <div id="loading">
        <i class="ioc-spinner ioc-spin"></i>
    </div>
    <?php print_unescaped($this->inc('part.content')); ?>
</div>
<div>
</div>
