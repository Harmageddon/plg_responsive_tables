<?php
/**
 * @package    ResponsiveTables
 *
 * @copyright  Copyright (C) 2015 Constantin Romankiewicz.
 * @license    Apache License 2.0; see LICENSE
 */

/**
 * This plugin modifies HTML tables to support responsive design.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class PlgContentResponsiveTables extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Adds data attributes at preparation of content.
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   mixed    &$item    An object with a "text" property holding the content.
	 * @param   mixed    $params   Additional parameters. See {@see PlgContentContent()}.
	 * @param   integer  $page     Optional page number. Unused. Defaults to zero.
	 *
	 * @return  boolean	True on success.
	 */
	public function onContentPrepare($context, &$item, $params, $page = 0)
	{
		// TODO: Context filtering

		// Quick check
		if (strpos($item->text, '<table') === false)
		{
			return null;
		}

		$regex /** @lang RegExp */ = "/<table(.*?)>\s*"   // <table>
				. "(<thead.*?>\s*"                        // optional: <thead>
					. "<tr.*?>(.*?)<\/tr>\s*"             //              <tr>
				. "<\/thead>)?\s*"                        //           </thead>
				. "(<tbody(.*?)>)?"                       // optional: <tbody>
				. "(.*?)"                                 // table body
				. "(<\/tbody>)?\s*"                       // optional: </tbody>
				. "<\/table>"                             // </table>
				. "/is";

		$item->text = preg_replace_callback($regex, 'PlgContentResponsiveTables::table', $item->text);

		return true;
	}

	/**
	 * Processes the matched table row by row.
	 *
	 * @param   array  $m  Matched string.
	 *                      array(
	 *                        0 => full match,
	 *                        1 => table attributes,
	 *                        2 => full thead,
	 *                        3 => thead tds,
	 *                        4 => tbody,
	 *                        5 => tbody attributes,
	 *                        6 => tbody content,
	 *                        7 => /tbody
	 *                      )
	 *
	 * @return   string  Processed result.
	 */
	public static function table($m)
	{
		$result = "<table" . $m[1] . ">\n";

		// Head
		if (!empty($m[2]))
		{
			$result .= $m[2];

			$regexHead /** @lang RegExp */ = "/<td[^>]*?>(.*?)<\/td>\s*/is";

			preg_match_all($regexHead, $m[3], $matches);

			if ($matches && $matches[1])
			{
				$heads = $matches[1];
			}
		}

		// Body
		if (!empty($m[6]))
		{
			$result .= $m[4];   // <tbody>

			$regexRow /** @lang RegExp */  = "/(<tr.*?>)\s*(.*?)\s*<\/tr>/is";
			$regexCell /** @lang RegExp */ = "/<td(.*?)>(.*?)<\/td>/is";


			// $rows: array(full match, tr + attributes, cells)
			if (preg_match_all($regexRow, $m[6], $rows) && isset($rows[0]) && isset($rows[1]) && isset($rows[2]))
			{
				$rowCount = count($rows[0]);

				for ($i = 0; $i < $rowCount; $i++) {
				    $result .= $rows[1][$i];

					// $cells: array(full match, attributes, content)
					if (preg_match_all($regexCell, $rows[2][$i], $cells))
					{
						$cellCount = count($cells[0]);

						for ($j = 0; $j < $cellCount; $j++) {
							// td + attributes
						    $result .= "<td" . $cells[1][$j];

							// data-th
							if (isset($heads))
							{
								$result .= " data-th=\"" . $heads[$j] . "\"";
							}

							// data-first-cell
							if ($j > 0)
							{
								$result .= " data-first-cell=\"" . $cells[2][0] . "\"";
							}

							$result .= ">\n" . $cells[2][$j] . "\n</td>\n";
						}

					}

					$result .= "</tr>\n";
				}

			}

			$result .= $m[7];   //</tbody>
		}

		$result .= "</table>\n";

		return $result;
	}

	public static function cells($m)
	{
		var_dump($m);
	}
}
