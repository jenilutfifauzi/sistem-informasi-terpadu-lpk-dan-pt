# Quickstart: CTK Index Status Display

**Feature**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)  
**For**: End Users (HR Administrators)  
**Date**: February 15, 2026

## What Changed?

The CTK (Calon Tenaga Kerja) list page has been simplified for faster candidate assessment:

### ✅ New: Simplified Status Column

**Before**: Status showed workflow stage names (MCU, Pembayaran, Soal/Berkas, etc.)  
**After**: Status shows completion summary

- **"Lengkap"** (green badge) = All 15 stages completed
- **"Belum Lengkap"** (orange badge) = Some stages still incomplete

### ❌ Removed: Tahap (Stage) Column

The separate "Stage" column has been removed since this information is already available in the Progress column.

### ✅ Unchanged: Progress Column

The detailed progress indicator remains:
- Shows "X/15" (e.g., "8/15 53%")
- Indicates exactly which stage number and completion percentage

## Quick Reference

### Understanding Status at a Glance

| Status Badge | Meaning | What to Do |
|--------------|---------|------------|
| 🟢 **Lengkap** | All 15 stages complete | Candidate ready - verify final documents |
| 🟠 **Belum Lengkap** | Some stages incomplete | Check Progress for details on what's missing |

### Reading Progress Information

**Format**: `X/15 Z%`

- **X** = Number of completed stages (0-15)
- **15** = Total stages in CTK process
- **Z%** = Completion percentage

**Examples**:
- `1/15 7%` = Very early stage (just started)
- `8/15 53%` = Halfway through process
- `14/15 93%` = Almost complete (one stage remaining)
- `15/15 100%` ✅ = Fully complete

## Common Tasks

### 1. Finding Complete Candidates

**Goal**: Quickly identify candidates ready for deployment

**Steps**:
1. Navigate to CTK list page
2. Scan the Status column for green **"Lengkap"** badges
3. Click on record to verify final details

**Tip**: Click the Status column header to sort - all complete candidates will group together.

---

### 2. Identifying Incomplete Candidates

**Goal**: Find candidates who need follow-up

**Steps**:
1. Look for orange **"Belum Lengkap"** badges in Status column
2. Check Progress column to see how far along they are
3. Click record to see which specific stages are pending

**Example Workflow**:
- See "Belum Lengkap" with "14/15 93%" → Almost done, prioritize
- See "Belum Lengkap" with "2/15 13%" → Early stage, monitor

---

### 3. Tracking Progress Over Time

**Goal**: Monitor candidate advancement through stages

**Steps**:
1. Open a candidate's detail page
2. Note current Progress (e.g., "8/15 53%")
3. Return to list later to check if Progress increased
4. Status will change to "Lengkap" when all 15 stages complete

---

### 4. Sorting by Completion Status

**Goal**: Group candidates by completion level

**Steps**:
1. Click **Status** column header
2. First click: Complete candidates appear first (or last, depending on default)
3. Click again to reverse order

**Alternate**: Sort by Progress column for more granular ordering (by stage count)

---

### 5. Filtering the List

**Available Filters** (unchanged from before):

- **Entitas**: Filter by LPK or PT
- **Status Pembayaran**: Filter by payment completion
- **Dibuat**: Filter by creation date range

**Note**: The "Tahap" filter has been removed. Use Progress column to visually identify stage levels.

---

## Visual Guide

### Table Layout (New)

```
┌─────────────┬──────────────┬─────────────────┬─────────┬──────────┬─────────────┬───────────┐
│ NIK         │ Nama Lengkap │ Status          │ Entitas │ Progress │ No. Telepon │ Dibuat    │
├─────────────┼──────────────┼─────────────────┼─────────┼──────────┼─────────────┼───────────┤
│ 1234567890  │ Jeni Lutfi   │ 🟠 Belum        │ 🔵 LPK  │ 1/15     │ 081388...   │ 15/02/... │
│             │              │    Lengkap      │         │ 7%       │             │           │
├─────────────┼──────────────┼─────────────────┼─────────┼──────────┼─────────────┼───────────┤
│ 0987654321  │ Ahmad Ali    │ 🟢 Lengkap      │ 🟠 PT   │ 15/15    │ 082199...   │ 10/02/... │
│             │              │                 │         │ 100%     │             │           │
└─────────────┴──────────────┴─────────────────┴─────────┴──────────┴─────────────┴───────────┘
```

### Before vs After Comparison

**Old Table** (8 columns):
```
NIK | Nama | Status (MCU/Pembayaran/etc) | Tahap (Stage 3) | Entitas | Progress | Phone | Date
```

**New Table** (7 columns):
```
NIK | Nama | Status (Lengkap/Belum) | Entitas | Progress | Phone | Date
```

---

## Frequently Asked Questions

### Q: Where did the stage names go?

**A**: The specific stage names (MCU, Pembayaran, Soal/Berkas, etc.) are still visible when you click into a candidate's detail page. The list view now shows overall completion status for faster scanning.

---

### Q: How do I know which stage a candidate is on?

**A**: Check the **Progress** column:
- The number "X/15" tells you how many stages are complete
- Click the candidate's row to see detailed stage breakdown

---

### Q: Can I still filter by stage?

**A**: The stage filter has been removed. Instead:
- Use Entity filter (LPK shows stages 1-5, PT shows stages 6-15)
- Visually scan Progress column for specific stage counts
- Use search to find candidates by name or NIK

---

### Q: What if I need to see the old Status field?

**A**: The workflow status (MCU, Pembayaran, etc.) is still stored in the database and visible on the candidate detail page. The list view now prioritizes completion status for easier assessment.

---

### Q: Why does "Lengkap" show even if current_stage is not 15?

**A**: "Lengkap" means **all stages are complete**, not just that the candidate has reached stage 15. A candidate could be at stage 10 but have all previous stages incomplete. "Lengkap" only appears when all 15 checkboxes are marked complete.

---

## Tips for Efficient Use

### 💡 Tip 1: Use Status for Quick Triage

Scan Status column first:
- Green = Ready for action
- Orange = Still in process

Then check Progress for details.

---

### 💡 Tip 2: Combine Entity + Status

Filter by Entity (e.g., "LPK"), then sort by Status to see all complete LPK candidates grouped together.

---

### 💡 Tip 3: Monitor Progress Trends

Candidates with high Progress (13/15, 14/15) are close to completion - prioritize follow-up on these.

---

### 💡 Tip 4: Use Search for Specific Candidates

If you know the candidate's name or NIK, use the search box instead of scanning the table.

---

## Troubleshooting

### Issue: Status shows "Belum Lengkap" but Progress shows "15/15 100%"

**Cause**: This should not occur - status is calculated from completed stages count.

**Solution**: 
1. Refresh the page
2. If persists, click into the candidate detail to verify stage completions
3. Contact system administrator if status remains incorrect

---

### Issue: Cannot find Tahap filter

**Expected Behavior**: The Tahap filter has been intentionally removed in this update.

**Workaround**: Use Entity filter + Progress visual scan as replacement.

---

### Issue: Export missing columns

**Expected Behavior**: Exported files will show "Lengkap/Belum Lengkap" in Status column and will not include Tahap column.

**Solution**: This is correct - exports match the new table structure.

---

## Need Help?

### View Candidate Details

Click any row in the table to open the full candidate profile with complete stage-by-stage breakdown.

### Contact Support

If you encounter issues or need assistance:
- Check the candidate detail page for full information
- Contact your system administrator
- Reference this quickstart guide

---

## Summary

**Key Takeaways**:

1. ✅ **Status column** now shows "Lengkap" (complete) or "Belum Lengkap" (incomplete)
2. ❌ **Tahap column** removed (info available in Progress column)
3. ✅ **Progress column** unchanged (still shows "X/15 Z%")
4. 🎯 **Benefit**: Faster candidate assessment at a glance

**Workflow**:
1. Scan Status for completion overview
2. Check Progress for stage details
3. Click row for full candidate information

---

**Last Updated**: February 15, 2026  
**Version**: 1.0.0  
**Applies To**: CTK Module (Calon Tenaga Kerja List Page)
