<?php

class MonthCalendar {

	private $events = array();
	private $month;

	public function __construct(DateTime $month) {
		$this->month = new DateTime();
		$this->month->setDate((int) $month->format("Y"), (int) $month->format("m"), 1);
		$this->month->setTime(0, 0, 0);
	}

	public function addEvent(DateTime $date, $text) {
		if ($date->format("Y m") != $this->month->format("Y m")) {
			throw new OutOfBoundsException("Attempt to add event outside of calendar's date range failed.");
		}
		$day = (int) $date->format("d");
		if (!isset($this->events[$day])) {
			$this->events[$day] = array();
		}
		$numDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $date->format("m"), $date->format("Y"));
		if ($day < 0 || $day > $numDaysInMonth) {
			throw new OutOfBoundsException("Attempt to add event outside of calendar's date range failed.");
		}
		array_push($this->events[$day], $text);
	}

	public function __toString() {
		$numDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->month->format("m"), $this->month->format("Y"));

		$firstDay = clone $this->month;
		$firstDayWeekday = (int) $firstDay->format("w");    // 0 = Sunday, 1 = Monday, ... 6 = Saturday

		$calendarCells = array();

		$daysBeforeMonth = $firstDayWeekday;
		while ($daysBeforeMonth > 0) {
			array_push($calendarCells, "");
			$daysBeforeMonth--;
		}

		$now = new DateTime();
		$today = (int) $now->format("d");
		$month = $now->format("m");
		$calendarMonth = $now->format("m");
		$isThisMonth = ($month == $calendarMonth);

		for ($i = 0; $i < $numDaysInMonth; $i++) {
			$day = $i + 1;
			$isToday = ($isThisMonth && $day == $today);
			$classes = "calendar-day-label";
			if ($isToday) {
				$classes .= " calendar-day-label-today";
			}
			$cell = "<span class=\"" . $classes . "\">" . $day . "</span>";
			if (isset($this->events[$day])) {
				$cell .= "<ul class=\"calendar-day-events\">";
				foreach ($this->events[$day] as $event) {
					$cell .= "<li class=\"calendar-day-event\">" . $event . "</li>";
				}
				$cell .= "</ul>";
			}
			array_push($calendarCells, $cell);
		}

		$daysAfterMonth = 7 - count($calendarCells) % 7;
		if ($daysAfterMonth < 7) {
			while ($daysAfterMonth > 0) {
				array_push($calendarCells, "");
				$daysAfterMonth--;
			}
		}

		$output = "<table class=\"calendar-month\">" . PHP_EOL;
		$output .= "<tr>" . PHP_EOL;
		$output .= "<th>Sun</th>" . PHP_EOL;
		$output .= "<th>Mon</th>" . PHP_EOL;
		$output .= "<th>Tue</th>" . PHP_EOL;
		$output .= "<th>Wed</th>" . PHP_EOL;
		$output .= "<th>Thu</th>" . PHP_EOL;
		$output .= "<th>Fri</th>" . PHP_EOL;
		$output .= "<th>Sat</th>" . PHP_EOL;
		$output .= "</tr>" . PHP_EOL;

		for ($i = 0; $i < count($calendarCells); $i++) {
			$mod = $i % 7;
			if ($mod == 0) {
				$output .= "<tr>" . PHP_EOL;
			}
			$output .= "<td>" . $calendarCells[$i] . "</td>" . PHP_EOL;
			if ($mod == 6) {
				$output .= "</tr>" . PHP_EOL;
			}
		}

		$output .= "</table>";

		return $output;
	}

}