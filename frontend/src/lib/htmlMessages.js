const HTML_MESSAGE_TAG_RE =
  /<\/?(a|b|blockquote|br|code|div|em|i|li|ol|p|pre|small|span|strong|sub|sup|table|tbody|td|th|thead|tr|u|ul)\b[^>]*>/i;

const ALLOWED_HTML_TAGS = new Set([
  'A',
  'B',
  'BLOCKQUOTE',
  'BR',
  'CODE',
  'DIV',
  'EM',
  'I',
  'LI',
  'OL',
  'P',
  'PRE',
  'SMALL',
  'SPAN',
  'STRONG',
  'SUB',
  'SUP',
  'TABLE',
  'TBODY',
  'TD',
  'TH',
  'THEAD',
  'TR',
  'U',
  'UL',
]);

const REMOVED_HTML_TAGS = new Set(['SCRIPT', 'STYLE', 'IFRAME', 'OBJECT', 'EMBED', 'LINK', 'META']);

export function isFullHtmlDocumentMessage(message) {
  if (message == null || typeof message !== 'string') return false;
  const t = message.trim();
  if (!t) return false;
  const lower = t.slice(0, 32).toLowerCase();
  return lower.startsWith('<!doctype') || lower.startsWith('<html') || (t.startsWith('<') && t.includes('<table'));
}

export function hasInlineHtmlMessage(message) {
  return typeof message === 'string' && HTML_MESSAGE_TAG_RE.test(message);
}

function isSafeUrl(value) {
  const raw = String(value || '').trim();
  if (!raw) return false;
  try {
    const base = typeof window !== 'undefined' ? window.location.origin : 'http://localhost';
    const url = new URL(raw, base);
    return ['http:', 'https:', 'mailto:', 'tel:'].includes(url.protocol);
  } catch {
    return false;
  }
}

export function sanitizeMessageHtml(message) {
  if (typeof document === 'undefined') return '';

  const template = document.createElement('template');
  template.innerHTML = String(message || '');

  function cleanNode(node) {
    if (node.nodeType === Node.COMMENT_NODE) {
      node.remove();
      return;
    }

    if (node.nodeType !== Node.ELEMENT_NODE) return;

    const tag = node.tagName;
    if (REMOVED_HTML_TAGS.has(tag)) {
      node.remove();
      return;
    }

    if (!ALLOWED_HTML_TAGS.has(tag)) {
      node.replaceWith(...Array.from(node.childNodes));
      return;
    }

    for (const attr of Array.from(node.attributes)) {
      const name = attr.name.toLowerCase();
      const allowed =
        (tag === 'A' && ['href', 'title'].includes(name)) ||
        (['TH', 'TD'].includes(tag) && ['colspan', 'rowspan'].includes(name));

      if (!allowed) {
        node.removeAttribute(attr.name);
        continue;
      }

      if (tag === 'A' && name === 'href' && !isSafeUrl(attr.value)) {
        node.removeAttribute(attr.name);
      }
    }

    if (tag === 'A' && node.getAttribute('href')) {
      node.setAttribute('target', '_blank');
      node.setAttribute('rel', 'noopener noreferrer');
    }
  }

  for (const node of Array.from(template.content.querySelectorAll('*'))) {
    cleanNode(node);
  }
  for (const node of Array.from(template.content.childNodes)) {
    cleanNode(node);
  }

  return template.innerHTML;
}
