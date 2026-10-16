"""Builds the layouts used by the compatibility tests (docs/yooarchive/DEVELOPING.md).

Each page type gets a marker text ("COMPAT-<type>") plus YOOtheme and Essentials for
YOOtheme Pro elements fed by the page source, so a broken source shows up as a missing
marker or empty element in the rendered HTML. Run: python make_layouts.py
"""
import json

V = "5.0.51"


def el(type_, props=None, source=None, children=None):
    node = {"type": type_, "props": props or {}}
    if source:
        node["source"] = source
    if children:
        node["children"] = children
    return node


def page(*elements):
    return {"type": "layout", "version": V, "children": [
        el("section", {"style": "default", "width": "default"}, children=[
            el("row", children=[el("column", {"width_bp": "m"}, children=list(elements))])
        ])
    ]}


def marker(name):
    return el("text", {"content": f"<p class=\"compat-marker\">COMPAT-{name}</p>"})


def single(query, props, args=None):
    q = {"name": query}
    if args:
        q["arguments"] = args
    return {"query": q, "props": props}


def grid(query, props, field=None, args=None, cls="compat-grid"):
    q = {"name": query}
    if field:
        q["field"] = {"name": field, "arguments": {}}
    if args:
        q["arguments"] = args
    return el("grid", {"grid_default": "1", "grid_medium": "3", "show_title": True, "show_content": True,
                       "show_meta": True, "show_link": True, "class": cls},
              children=[el("grid_item", {}, source={"query": q, "props": props})])


def essentials(query, title_field="title", link_field="link", args=None, prefix=""):
    """Essentials markdown + social sharing fed by the given single-item source."""
    q = {"name": query}
    if args:
        q["arguments"] = args
    md = el("yooessentials_markdown", {"content": "", "class": "compat-md"},
            source={"query": q, "props": {"content": {"name": prefix + title_field, "filters": {"before": "## MD: "}}}})
    share = el("yooessentials_social_sharing", {"link_style": "button", "class": "compat-share"}, children=[
        el("yooessentials_social_sharing_item", {"network": "facebook"},
           source={"query": q, "props": {"shared_url": {"name": prefix + link_field}, "shared_text": {"name": prefix + title_field}}})
    ])
    return [md, share]


CSV = grid("csvA1b2c3", {"title": {"name": "comune"}, "meta": {"name": "provincia"},
                         "content": {"name": "abitanti", "filters": {"before": "Abitanti: "}}},
           field="records", cls="compat-csv")

layouts = {
    # Our template type, with Essentials elements fed by our sources and an Essentials
    # "datemodify" filter applied to a field of our source.
    "archive": page(
        marker("archive"),
        el("headline", {"title_element": "h1", "content": "Archivio"},
           source=single("yooarchive", {"content": {"name": "categories", "filters": {"before": "Archivio: "}}})),
        el("text", {"content": ""}, source=single("yooarchive", {"content": {"name": "total", "filters": {"before": "Totale: "}}})),
        el("html", {"content": ""}, source=single("yooarchive", {"content": {"name": "filterForm"}})),
        grid("yooarchiveArticles", {"title": {"name": "title"}, "link": {"name": "link"},
                                    "meta": {"name": "field.comune.name"},
                                    "content": {"name": "created", "filters": {"date": "Y-m-d", "datemodify": "+1 year", "before": "Data +1 anno: "}}},
             args={"order": "created", "order_direction": "DESC"}, cls="compat-archive"),
        *essentials("yooarchiveArticle", args={"offset": 0}),
        CSV,
        el("pagination", {"pagination_type": "numeric", "text_align": "center"}),
    ),
    "article": page(
        marker("article"),
        el("headline", {"title_element": "h1", "content": ""}, source=single("article", {"content": {"name": "title"}})),
        *essentials("article"),
        CSV,
    ),
    "category": page(
        marker("category"),
        el("headline", {"title_element": "h1", "content": ""}, source=single("category", {"content": {"name": "title"}})),
        grid("articles", {"title": {"name": "title"}, "link": {"name": "link"}}, cls="compat-category"),
        CSV,
        el("pagination", {"pagination_type": "numeric"}),
    ),
    "featured": page(
        marker("featured"),
        grid("articles", {"title": {"name": "title"}, "link": {"name": "link"}}, cls="compat-featured"),
        CSV,
    ),
    "search": page(
        marker("search"),
        el("headline", {"title_element": "h1", "content": ""}, source=single("smartSearch", {"content": {"name": "searchword", "filters": {"before": "Ricerca: "}}})),
        grid("smartSearchItems", {"title": {"name": "title"}, "link": {"name": "route"}}, cls="compat-search"),
    ),
}

for name, layout in layouts.items():
    with open(f"{name}.json", "w", encoding="utf-8", newline="\n") as fh:
        json.dump(layout, fh, indent=1, ensure_ascii=False)
        fh.write("\n")
print("layouts:", ", ".join(layouts))
