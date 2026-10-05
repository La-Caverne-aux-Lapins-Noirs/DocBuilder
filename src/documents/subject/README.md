# Subject context

A subject Dabsic file describes pedagogical content. Context that belongs to the
current Infosphere instance is expected to be injected at generation time instead
of being hard-coded in the subject.

Infosphere currently injects these main scopes:

- `Language`: current document language;
- `School`: current school identity, including `Name`, `Codename`, `Logo` and
  `DocumentLogo` when available;
- `Matter`: parent activity identity (`FR`, `EN`, `Codename`, `Code`,
  `TemplateCodename`, `Description`);
- `Activity`: current activity identity with the same fields and its current icon
  as `Logo` when available;
- `Laboratory`: laboratory associated with the subject, including `FR`, `EN`,
  `Codename`, `Logo` and `Source`;
- `Login`, `Token`, `TeamSize` and `Delivery`: per-user activity instance data.

When a subject configuration itself comes from an activity template, the
laboratory attached to that template is preferred for `Laboratory`. Otherwise a
direct laboratory assignment wins before inherited assignments.

## Front page

`FrontPage` is intentionally optional. If it is absent, the renderer uses the
injected activity name and description.

A model can override either value independently:

```dabsic
[FrontPage
  [Message
    FR = "Titre libre"
  ]
  [Description
    FR = Activity.Description.FR
  ]
]
```

The first front-page logo is the current school logo. The second is selected in
this order:

1. `FrontPage.Logo`;
2. `Laboratory.Logo`;
3. `Activity.Logo`;
4. legacy `Matter.Logo`.

`FrontPage.SchoolLogo` can explicitly replace the school logo for a model that
needs a special first image.

The legacy `SchoolName`, `Matter.Logo`, `Matter.SmallLogo`, `Activity.Logo` and
`Activity.SmallLogo` fields remain supported for old subject files, but new
subjects should not hard-code tenant or activity metadata there.
