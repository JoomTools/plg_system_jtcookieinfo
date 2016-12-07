<?php
/**
 * Plugin for Joomla! 2.5 and higher
 *
 * Displays a pop-up window with information about the use of cookies.
 *
 * @package     Joomla.Plugin
 * @subpackage  System.jtcookieinfo
 * @author      Guido De Gobbis <guido.de.gobbis@joomtools.de>
 * @copyright   2015 JoomTools
 * @license     GNU/GPLv3 <http://www.gnu.org/licenses/gpl-3.0.de.html>
 * @link        http://joomtools.de
 */

// No direct access
defined('_JEXEC') or die;

$jtci = $this->jtci; ?>

<div class="jtci">
	<div class="uk-alert uk-alert-large <?php echo ($jtci->messageType != '') ? ' uk-alert-' . $jtci->messageType : ''; ?>" data-uk-alert>
		<a title="<?php echo $jtci->closeTitle; ?>" class="uk-alert-close uk-close close" href="#"></a>
		<?php if ($jtci->setTitle) : ?>
			<h4 class="jtci-heading uk-h2"><?php echo $jtci->title; ?></h4>
		<?php endif; ?>
		<div class="jtci-message">
			<?php echo $jtci->message; ?>
			<?php if ($jtci->legalURL) : ?>
				<a class="jtci-legal<?php echo ($jtci->messageType) ? ' uk-text-' . $jtci->messageType : ''; ?>" title="<?php echo $jtci->legalTitle; ?>" href="<?php echo JRoute::_('index.php?Itemid=' . (int) $jtci->legalURL); ?>">
					<?php echo $jtci->legalLabel; ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
