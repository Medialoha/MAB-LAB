<?php defined('DIRECT_ACCESS_CHECK') or die('DIRECT ACCESS NOT ALLOWED');
/**
 * Copyright (c) 2013 EIRL DEVAUX J. - Medialoha.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the GNU Public License v3.0
 * which accompanies this distribution, and is available at
 * http://www.gnu.org/licenses/gpl.html
 *
 * Contributors:
 *     EIRL DEVAUX J. - Medialoha - initial API and implementation
 *     MAREK MASLANKA - Logintar - added review reports by the same errors and formatting logcat
 */

class LogcatLineHelper {
	
	public static function formatLine($logcatLine)
	{
		$color = LogcatLineHelper::getColorForType($logcatLine->type);
		return '<span style="color:'.$color.'">'.$logcatLine->time.' '.$logcatLine->date.' '.$logcatLine->tag.' '.$logcatLine->message.'</span>';
	}
	
	public static function formatLineAsTableRow($logcatLine)
	{
		$color = LogcatLineHelper::getColorForType($logcatLine->type);
		return sprintf('
		<tr style="color:%s">
			<td>
				<span style="display:block;white-space:nowrap;width:140px">%s&nbsp;%s</span>
			</td>
			<td>
				%s
			</td>
			<td style="text-align:center">
				%s
			</td>
			<td>
				<span style="white-space:nowrap;">%s</span>
			</td>
		</tr>
		', $color, $logcatLine->time, $logcatLine->date, $logcatLine->tag, $logcatLine->type, $logcatLine->message);
	}
	
	private static function getColorForType($type)
	{
		switch ($type)
		{
			case 'I':
				return '#007A00';
			case 'W':
				return '#FFAF00';
			case 'E':
				return '#FF0000';
			case 'D':
				return '#00007F';
			case 'V':
				return '#000000';
			default:
				return '#000000';
		}
	}
}