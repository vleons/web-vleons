<?php

class Task2
{
	private string $text;
	private string $result = '';

	public function __construct(string $text)
	{
		$this->text = $text;
	}

	public function execute()
	{
		$images = [];
		preg_match_all("/<img [^>]*>/", $this->text, $images);

		foreach ($images[0] as $key => $image) {

			if ($image)
				$this->result .= '<br>Картинка №' . $key + 1 . '<br>' . $image;
		}
	}

	public function getResult() : string
	{
		return $this->result;
	}

}