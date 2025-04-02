<?php

class Task20
{
	private string $text;
	private string $result = '';
	private string $styleHtml = '';

	public function __construct(string $text)
	{
		$this->text = $text;
	}

	public function execute()
	{
		$this->styleHtml .= '<style type="text/css">';

		$pattern1 = '<((?!class).)*(?= style="(?<style>[^"]*)")((?!class).)*>'; //  <div style="1234">
		$pattern2 = '<[^<>]*(?=(?= class="(?<class2>[^"]*)")[^<>]* style="(?<style2>[^"]*)")[^<>]*>'; // <div class="1234" style="123">
		$pattern3 = '<[^<>]*(?= style="(?<style3>[^"]*)"[^<>]*(?= class="(?<class3>[^"]*)"))[^<>]*>'; // <div style="123" class="1234">
		$regexResult = [];
		preg_match_all('/' . $pattern1 . '|' . $pattern2 . '|' . $pattern3 . '/', $this->text, $regexResult, PREG_SET_ORDER, 0);

		$groupStyles = [];
		foreach ($regexResult as $item) {
			$elementHtml = $item[0];

			$class= '';
			if (isset($item['class2']) && strlen($item['class2'])) {
				$class = $item['class2'];
			} elseif (isset($item['class3']) && strlen($item['class3'])) {
				$class = $item['class3'];
			}

			$style = '';
			if (isset($item['style']) && strlen($item['style'])) {
				$style = $item['style'];
			} elseif (isset($item['style2']) && strlen($item['style2'])) {
				$style = $item['style2'];
			} elseif (isset($item['style3']) && strlen($item['style3'])) {
				$style = $item['style3'];
			}

			$groupStyles[$style][] = [
				'html' => $elementHtml,
				'class' => $class,
				'style' => $style,
			];
		}

		foreach ($groupStyles as $style => $groupStyle) {
			if (count($groupStyle) < 2){
				unset($groupStyles[$style]);
			}
		}

		foreach ($groupStyles as $style => &$groupStyle) {
			$className = 'cl' . mt_rand();
			$style = '.' . $className . '{ ' . $style . ' } ';
			$this->styleHtml .= $style;
			foreach ($groupStyle as &$element) {
				if (strlen($element['class'])) {
					$element['new_html'] =  str_replace(
						'style="' . $element['style'] . '"',
						'class="' . $className . ' ' . $element['class'] . '"',
						$element['html']
					);
				} else {
					$element['new_html'] = str_replace(
						'style="' . $element['style'] . '"',
						'class="' . $className . '"',
						$element['html']
					);
				}

				$element['new_html'] = str_replace('class="' . $element['class'] . '"', '', $element['new_html']);

				unset($element);
			}
			unset($groupStyle);
		}

		foreach ($groupStyles as $groupStyle) {
			foreach ($groupStyle as $element) {
				$this->text = str_replace($element['html'], $element['new_html'], $this->text);
			}
		}


		$this->styleHtml .= '</style>';

		$this->text = $this->styleHtml . ' ' . $this->text;

		$this->result = $this->text;
	}

	public function getResult(): string
	{
		return $this->result;
	}

	public function getStyleHtml(): string
	{
		return $this->styleHtml;
	}

}