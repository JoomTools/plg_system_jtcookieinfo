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

jimport('joomla.plugin.plugin');

/**
 * Class PlgSystemJtcookieinfo
 *
 * Displays a pop-up window with information about the use of cookies.
 *
 * @package     Joomla.Plugin
 * @subpackage  System.jtcookieinfo
 * @since       2.5
 */
class PlgSystemJtcookieinfo extends JPlugin
{
	protected $jtci;

	/**
	 * onBeforeRender
	 *
	 * @return  void
	 */
	public function onBeforeRender()
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			return;
		}

		$cookie   = $app->input->cookie->getBool('jtci_accept', false);
		$position = ($this->params->get('jtci_set_position', 'top') == 'bottom') ?
			'.css({top:"inherit",bottom:0})' : '';

		$script = '
			jQuery(function ($) {
				$(".jtci").hide();
				if ($.cookie("jtci_accept") == undefined) {
					$(".jtci")' . $position . '.delay(800).show("slow");
				}
				$(".jtci-close").on("click", function () {
					$.cookie("jtci_accept", true, {expires: ' . (int) $this->params->get('jtci_expire', '365') . '});
					$(".jtci").hide("slow");
				});
			});
		';

		if ($cookie === false)
		{
			$document = JFactory::getDocument();

			if (version_compare(JVERSION, '3', 'lt'))
			{
				if (JFactory::getApplication()->get('jquery') !== true)
				{
					$document->addScript("//code.jquery.com/jquery-latest.min.js");
					JFactory::getApplication()->set('jquery', true);
				}
			}

			$min = (JDEBUG) ? '' : '.min';

			JHtml::_('script', 'plugins/system/jtcookieinfo/assets/jquery.cookie' . $min . '.js');
			JHtml::_('stylesheet', 'plugins/system/jtcookieinfo/assets/jtcookieinfo' . $min . '.css');
			$document->addScriptDeclaration($script);
		}
	}

	/**
	 * onAfterRender
	 *
	 * @return  void
	 */
	public function onAfterRender()
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			return;
		}

		$this->loadLanguage('plg_system_jtcookieinfo');

		$jtci        = new stdClass;
		$messageType = array(
			'bs3'   => array(
				'error' => 'danger'
			),
			'uikit' => array(
				'info'  => '',
				'error' => 'danger'
			),
		);

		$jtci->theme      = $this->params->get('jtci_theme', 'default');
		$jtci->setTitle   = $this->params->get('jtci_set_title', 1);
		$jtci->title      = $this->params->get('jtci_title', JText::_('PLG_SYSTEM_JTCOOKIEINFO_TITLE'));
		$jtci->message    = $this->params->get('jtci_message', JText::_('PLG_SYSTEM_JTCOOKIEINFO_MESSAGE'));
		$jtci->closeTitle = $this->params->get('jtci_close_title', JText::_('PLG_SYSTEM_JTCOOKIEINFO_CLOSE_TITLE'));
		$jtci->legalURL   = $this->params->get('jtci_legal_url', '');
		$jtci->legalLabel = $this->params->get('jtci_legal_label', JText::_('PLG_SYSTEM_JTCOOKIEINFO_LEGAL_LABEL'));
		$jtci->legalTitle = $this->params->get('jtci_legal_title', JText::_('PLG_SYSTEM_JTCOOKIEINFO_LEGAL_TITLE'));

		if (isset($messageType[$jtci->theme][$this->params->get('jtci_message_type', 'dark')]))
		{
			$jtci->messageType = $messageType[$jtci->theme][$this->params->get('jtci_message_type', 'dark')];
		}
		else
		{
			$jtci->messageType = $this->params->get('jtci_message_type', 'dark');
		}

		$this->jtci = $jtci;

		$strOutputHTML = $this->getTmpl($jtci->theme);

		$cookie = $app->input->cookie->get('jtci_accept', false, 'boolean');

		if ($cookie === false)
		{
			if (version_compare(JVERSION, '3', 'lt'))
			{
				$body = JResponse::getBody();
				$body = str_replace('</body>', $strOutputHTML . '</body>', $body);
				JResponse::setBody($body);
			}
			else
			{
				$body = $app->getBody();
				$body = str_replace('</body>', $strOutputHTML . '</body>', $body);
				$app->setBody($body);
			}
		}
	}

	/**
	 * getTmpl
	 *
	 * @param   string  $theme  Name of output templatefile without type
	 *
	 * @return string Templateoutput from selected framework
	 */
	protected function getTmpl($theme)
	{
		$path = $this->getTmplPath($theme);

		// Start capturing output into a buffer
		ob_start();

		// Include the requested template filename in the local scope
		include "$path";

		// Done with the requested template; get the buffer and
		// clear it.
		$return = ob_get_contents();
		ob_end_clean();

		return $return;
	}

	/**
	 * getTmplPath
	 *
	 * @param   string  $filename  Name of output templatefile without type
	 * @param   string  $type      Type of templatefile
	 *
	 * @return string Path to output templatefile
	 */
	protected function getTmplPath($filename, $type = 'php')
	{
		$template = JFactory::getApplication()->getTemplate();

		// Build the template and base path for the layout
		$tAbsPath = JPATH_THEMES . '/' . $template . '/html/plg_' . $this->_type . '_' . $this->_name . '/' . $filename . '.' . $type;
		$bAbsPath = JPATH_BASE . '/plugins/' . $this->_type . '/' . $this->_name . '/tmpl/' . $filename . '.' . $type;
		$dAbsPath = JPATH_BASE . '/plugins/' . $this->_type . '/' . $this->_name . '/tmpl/default.' . $type;

		// If the template has a layout override use it
		switch (true)
		{
			case (file_exists($tAbsPath)):
				return $tAbsPath;
				break;
			case (file_exists($bAbsPath)):
				return $bAbsPath;
				break;
			default:
				return $dAbsPath;
				break;
		}
	}
}
