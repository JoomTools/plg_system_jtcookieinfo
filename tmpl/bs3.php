<?php
/**
 * Plugin for Joomla! 2.5 and higher
 *
 * Displays a pop-up window with information about the use of cookies.
 *
 * @package    Joomla.Plugin
 * @subpackage Content.jtcookieinfo
 * @author     Guido De Gobbis <guido.de.gobbis@joomtools.de>
 * @copyright  2015 JoomTools
 * @license    GNU/GPLv3 <http://www.gnu.org/licenses/gpl-3.0.de.html>
 * @link       http://joomtools.de
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
$jtci = $this->jtci; ?>

<div class="jtci bs3">
	<div class="bg-<?php echo $jtci->messageType; ?>">
		<a class="jtci-close close" title="<?php echo $jtci->closeTitle; ?>" href="#" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</a>
		<?php if ($jtci->setTitle) : ?>
			<h4 class="jtci-header"><?php echo $jtci->title; ?></h4>
		<?php endif; ?>
		<div class="jtci-message text-<?php echo $jtci->messageType; ?>">
			<?php echo $jtci->message; ?>
			<?php if ($jtci->legalURL) : ?>
				<a class="jtci-legal alert-link" title="<?php echo $jtci->legalTitle; ?>" href="<?php echo $jtci->legalURL; ?>">
					<?php echo $jtci->legalLabel; ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
