<?php

/**
 * Интерфейс отображения
 */
interface Drawable
{
    /**
     * @param DrawInterface $device
     */
    public function draw(DrawInterface $device): void;
}

/**
 * Интерфейс зуммирования
 */
interface Zoomable
{
    /**
     * @param int $zoom
     */
    public function zoom(int $zoom): void;
}


/**
 * Интерфейс отображения точки
 */
interface DrawInterface
{
    /**
     * Отображение точки цветом $color по координатам ($x, $y)
     *
     * @param int $x
     * @param int $y
     * @param int $color
     */
    public function drawPixel(int $x, int $y, int $color): void;
}

/**
 * Класс дисплея
 */
class Display implements DrawInterface
{
    /**
     * @param int $x
     * @param int $y
     * @param int $color
     */
    public function drawPixel(int $x, int $y, int $color): void
    {
        /*
            Отображение точки цветом $color по координатам ($x, $y) на дисплей
         */
    }
}

/**
 * Класс принтера
 */
class Printer implements DrawInterface
{
    /**
     * @param int $x
     * @param int $y
     * @param int $color
     */
    public function drawPixel(int $x, int $y, int $color): void
    {
        /*
            Отображение точки цветом $color по координатам ($x, $y) на принтер
         */
    }
}

/**
 * Абстрактный класс фигуры
 */
abstract class Figure implements Drawable, Zoomable
{
    /** @var int */
    private $color;

    /** @var int */
    private $zoom;

    /** @var int */
    private $x;

    /** @var int */
    private $y;

    /**
     * Абстрактный метод для просчета точе отображения фигуры
     *
     * @return array
     */
    abstract public function createFigurePoints(): array;

    /**
     * Figure constructor
     *
     * @param int $x
     * @param int $y
     * @param int $zoom
     * @param int $color
     */
    protected function __construct(int $x, int $y, int $zoom, int $color)
    {
        $this->x = $x;
        $this->y = $y;
        $this->zoom = $zoom;
        $this->color = $color;
    }

    /**
     * @param DrawInterface $device
     */
    public function draw(DrawInterface $device): void
    {
        $points = $this->createFigurePoints();

        foreach ($points as $point) {
            $device->drawPixel($point['x'], $point['y'], $this->color);
        }
    }

    /**
     * @param int $x
     * @param int $y
     */
    public function moveTo(int $x, int $y): void
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @param int $zoom
     */
    public function zoom(int $zoom): void
    {
        $this->zoom = $zoom;
    }

    /**
     * @param int $color
     */
    public function setColor(int $color): void
    {
        $this->color = $color;
    }

}

/**
 * Класс прямоугольника
 */
class Rectangle extends Figure
{
    /** @var int */
    private $width;

    /** @var int */
    private $height;

    /**
     * Rectangle constructor
     *
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     * @param int $zoom
     * @param int $color
     */
    public function __construct(int $width, int $height, int $x, int $y, int $zoom, int $color)
    {
        parent::__construct($x, $y, $zoom, $color);

        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @return array
     */
    public function createFigurePoints(): array
    {
        /*
            Просчет всех координат для прямоугольника шириной $this->width и высотой $this->height
            с координатами верхнего левого угла в точке ($this->x, $this->>y),
            зумом $this->zoom
        */

        return $points;
    }
}

/**
 * Класс треугольника
 */
class Triangle extends Figure
{
    /** @var int */
    private $width;

    /** @var int */
    private $height;

    /** @var float */
    private $angle;

    /**
     * Triangle constructor
     *
     * @param int $width
     * @param int $height
     * @param float $angle
     * @param int $x
     * @param int $y
     * @param int $zoom
     * @param int $color
     */
    public function __construct(int $width, int $height, float $angle, int $x, int $y, int $zoom, int $color)
    {
        parent::__construct($x, $y, $zoom, $color);

        $this->width = $width;
        $this->height = $height;
        $this->angle = $angle;
    }

    /**
     * @return array
     */
    public function createFigurePoints(): array
    {
        /*
            Просчет всех координат для треугольника шириной $this->width и высотой $this->height,
            углом меджу сторонами $this->angle,
            с координатами угла в точке ($this->x, $this->>y),
            зумом $this->zoom
        */

        return $points;
    }

}

/**
 * Класс окружности
 */
class Circle extends Figure
{
    /** @var int */
    private $radius;

    /**
     * Circle constructor
     *
     * @param int $radius
     * @param int $x
     * @param int $y
     * @param int $zoom
     * @param int $color
     */
    public function __construct(int $radius, int $x, int $y, int $zoom, int $color)
    {
        parent::__construct($x, $y, $zoom, $color);

        $this->radius = $radius;
    }

    /**
     * @return array
     */
    public function createFigurePoints(): array
    {
        /*
            Просчет всех координат для окружности радиусом $this->radius
            с координатами центра окружности в точке ($this->x, $this->>y),
            зумом $this->zoom
        */

        return $points;
    }

}

/**
 * Класс редактора
 */
final class Editor implements Drawable, Zoomable
{
    /**
     * @var Editor
     */
    private static $instance = null;

    /** @var Figure[] */
    private $figures;

    /**
     * @var int
     */
    private $figuresCount;

    /**
     * Editor constructor.
     */
    private function __construct()
    {
        $this->figures = [];
        $this->figuresCount = 0;
    }

    private function __clone()
    {
        // Запрет клонирования
    }

    private function __wakeup()
    {
        // Запрет десириализации
    }

    /**
     * Создание экземпляра Editor
     * Singleton
     * Static factory Method
     *
     * @return Editor
     */
    static public function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Добавление фигуры
     * Возвращает идентификатор фигуры
     *
     * @param Figure $figure
     * @return int
     */
    public function add(Figure $figure): int
    {
        $this->figures[$this->figuresCount] = $figure;

        return $this->figuresCount++;
    }

    /**
     * Поис фигуры по идентификатору
     *
     * @param int $figureKey
     * @return Figure|null
     */
    public function find(int $figureKey)
    {
        return $this->figures[$figureKey] ?? null;
    }

    /**
     * Получить все фигуры
     *
     * @return Figure[]
     */
    public function getFigures(): array
    {
        return $this->figures;
    }

    /**
     * Отображение всех фигур на устройстве
     *
     * @param DrawInterface $device
     */
    public function draw(DrawInterface $device): void
    {
        foreach ($this->figures as $figure) {
            $figure->draw($device);
        }
    }

    /**
     * Изменение зума для всех фигур
     *
     * @param int $zoom
     */
    public function zoom(int $zoom): void
    {
        foreach ($this->figures as $figure) {
            $figure->zoom($zoom);
        }
    }
}

/**
 * Простая фабрика (Static Factory Method)
 */
class FigureFactory
{
    /**
     * Создание экземпляра Rectangle
     *
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     * @param int $zoom
     * @param int $color
     * @return Figure
     */
    static function createRectangle(int $width, int $height, int $x, int $y, int $zoom, int $color): Figure
    {
        return new Rectangle($width, $height, $x, $y, $zoom, $color);
    }

    /**
     * Создание экземпляра Triangle
     *
     * @param int $width
     * @param int $height
     * @param float $angle
     * @param int $x
     * @param int $y
     * @param int $zoom
     * @param int $color
     * @return Figure
     */
    static function createTriangle(int $width, int $height, float $angle, int $x, int $y, int $zoom, int $color): Figure
    {
        return new Triangle($width, $height, $angle, $x, $y, $zoom, $color);
    }

    /**
     * Создание экземпляра Circle
     *
     * @param int $radius
     * @param int $x
     * @param int $y
     * @param int $zoom
     * @param int $color
     * @return Figure
     */
    static function createCircle(int $radius, int $x, int $y, int $zoom, int $color): Figure
    {
        return new Circle($radius, $x, $y, $zoom, $color);
    }
}

/**
 * Использование
 */

// Создание экземпляра Editor
$editor = Editor::getInstance();

// Создание экземпляра прямоугольника из фабрики
$rectangle1 = FigureFactory::createRectangle(10, 5, 0, 0, 1, 0xffffff);
// Добавить в редактор
$editor->add($rectangle1);

// Создание экземпляра треугольника из фабрики
$triangle = FigureFactory::createTriangle(5, 5, 90.5, 10, 15, 1, 0xffffee);
// Добавить в редактор
$editor->add($triangle);

// Создание экземпляра окружности из фабрики
$circle = FigureFactory::createCircle(15, 20, 25, 1, 0xffffcc);
// Добавить в редактор
$circleId = $editor->add($circle);

// Создание экземпляра дисплея
$display = new Display();
// Создание экземпляра принтера
$printer = new Printer();

// Отобразить фигуры на дисплее
$editor->draw($display);

// Изменить зумдля всех фигур
$editor->zoom(2);

// Изменить зум только для треугольников
foreach (Editor::getInstance()->getFigures() as $figure) {
    if ($figure instanceof Triangle) {
        $figure->zoom(3);
    }
}

// Найти ранее созаднную фигуру окружности
$circle2 = $editor->find($circleId);
// Переместить по новым координатам
$circle2->moveTo(100, 100);

// Отобразить фигуры на принтере
$editor->draw($printer);




