# Branch & cPanel deployment guide

## Branches

| Branch | Holds | cPanel? |
|--------|-------|---------|
| `claude/dog-father-branch-structure-gtwotc` (editing) | Everything: theme files in `theme/`, plugin files in `plugins/` | No — work/edit here only |
| `theme-deploy` | The **theme** at the branch root (`style.css`, `functions.php`, …) | Yes |
| `plugins-deploy` | The **plugin** at the branch root | Yes |

The deploy branches keep files at the **root** so cPanel pulls them straight
into a named folder — no `.cpanel.yml`, no deploy step, no path editing.

## cPanel setup (do once per branch)

cPanel → **Git Version Control** → **Create**

**Theme:**
- Clone URL: `https://github.com/itwashraf/New-dog-father-website-.git`
- Repository Path: `/home/USERNAME/public_html/wp-content/themes/dog-father`
- Branch: `theme-deploy`

**Plugin:**
- Clone URL: `https://github.com/itwashraf/New-dog-father-website-.git`
- Repository Path: `/home/USERNAME/public_html/wp-content/plugins/dog-father-control-center`
- Branch: `plugins-deploy`

Replace `USERNAME` with your cPanel username. After creation, use **Manage →
Pull or Deploy** (or enable auto-pull) to update the live site.

## Workflow

1. Edit on the editing branch. Theme files live in `theme/`, plugin files in `plugins/`.
2. When ready, the `theme/` contents are synced to the root of `theme-deploy`,
   and `plugins/` contents to the root of `plugins-deploy`, then pushed.
3. cPanel pulls the deploy branch → live site updates.

> The sync in step 2 is a flatten (subfolder → branch root). Ask Claude to
> "deploy theme" / "deploy plugin" and it will sync + push the deploy branch.
