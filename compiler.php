<?php

class DomCompiler {
    private string $templatePath;
    private string $cacheDir;
    private ?DOMDocument $dom = null;
    private ?DOMXPath $xpath = null;
    private array $replacements = [];
    private int $markerCount = 0;

    public function __construct(string $templatePath, string $cacheDir = __DIR__ . '/build-template') {
        $this->templatePath = $templatePath;
        $this->cacheDir = $cacheDir;
        
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    private function isCacheValid(string $cacheFile): bool {
        if (!file_exists($cacheFile)) return false;
        return filemtime($this->templatePath) <= filemtime($cacheFile);
    }

    private function getMarker(): string {
        $this->markerCount++;
        return "@@__FST_MARKER_{$this->markerCount}__@@";
    }

    private function loadDom(): void {
        if ($this->dom !== null) return;
        
        $this->dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $html = file_get_contents($this->templatePath);
        if ($html) {
            // Encode as UTF-8
            $this->dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        }
        libxml_clear_errors();
        $this->xpath = new DOMXPath($this->dom);
    }

    public function setText(string $selector, string $phpVariable, ?DOMNode $contextNode = null): self {
        $this->loadDom();
        $nodes = $contextNode ? $this->xpath->query($selector, $contextNode) : $this->xpath->query($selector);
        
        if ($nodes !== false) {
            foreach ($nodes as $node) {
                $marker = $this->getMarker();
                $node->nodeValue = $marker;
                $this->replacements[$marker] = "<?= htmlspecialchars({$phpVariable} ?? '', ENT_QUOTES, 'UTF-8') ?>";
            }
        }
        return $this;
    }

    public function setAttribute(string $selector, string $attribute, string $phpVariable, ?DOMNode $contextNode = null): self {
        $this->loadDom();
        $nodes = $contextNode ? $this->xpath->query($selector, $contextNode) : $this->xpath->query($selector);
        
        if ($nodes !== false) {
            foreach ($nodes as $node) {
                if ($node instanceof DOMElement) {
                    $marker = $this->getMarker();
                    $node->setAttribute($attribute, $marker);
                    $this->replacements[$marker] = "<?= htmlspecialchars({$phpVariable} ?? '', ENT_QUOTES, 'UTF-8') ?>";
                }
            }
        }
        return $this;
    }

    public function setLoop(string $containerSelector, string $itemSelector, string $arrayVariable, string $alias, callable $callback): self {
        $this->loadDom();
        
        $containers = $this->xpath->query($containerSelector);
        if ($containers !== false) {
            foreach ($containers as $container) {
                $items = $this->xpath->query($itemSelector, $container);
                if ($items !== false && $items->length > 0) {
                    $templateNode = $items->item(0);
                    
                    $subCompiler = new class($this, $templateNode) {
                        private $compiler;
                        private $node;
                        public function __construct($compiler, $node) {
                            $this->compiler = $compiler;
                            $this->node = $node;
                        }
                        public function setText(string $selector, string $phpVariable) {
                            $this->compiler->setText($selector, $phpVariable, $this->node);
                            return $this;
                        }
                        public function setAttribute(string $selector, string $attribute, string $phpVariable) {
                            $this->compiler->setAttribute($selector, $attribute, $phpVariable, $this->node);
                            return $this;
                        }
                    };
                    
                    $callback($subCompiler);
                    
                    $startMarker = $this->getMarker();
                    $endMarker = $this->getMarker();
                    
                    $this->replacements[$startMarker] = "<?php foreach ({$arrayVariable} as {$alias}): ?>";
                    $this->replacements[$endMarker] = "<?php endforeach; ?>";
                    
                    $container->insertBefore($this->dom->createTextNode($startMarker), $templateNode);
                    if ($templateNode->nextSibling) {
                        $container->insertBefore($this->dom->createTextNode($endMarker), $templateNode->nextSibling);
                    } else {
                        $container->appendChild($this->dom->createTextNode($endMarker));
                    }
                    
                    for ($i = 1; $i < $items->length; $i++) {
                        $container->removeChild($items->item($i));
                    }
                }
            }
        }
        return $this;
    }

    public function compile(): string {
        $cacheFile = $this->cacheDir . '/' . basename($this->templatePath) . '.php';

        if ($this->isCacheValid($cacheFile)) {
            return $cacheFile;
        }

        $this->loadDom();
        $html = $this->dom->saveHTML();
        
        $html = str_replace('<?xml encoding="utf-8" ?>', '', $html);

        foreach ($this->replacements as $marker => $phpCode) {
            $html = str_replace($marker, $phpCode, $html);
        }

        file_put_contents($cacheFile, $html);
        return $cacheFile;
    }

    public function render(array $data): void {
        $cacheFile = $this->compile();
        extract($data);
        require $cacheFile;
    }
}
