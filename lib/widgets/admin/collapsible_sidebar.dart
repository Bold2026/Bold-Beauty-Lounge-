import 'package:flutter/material.dart';

/// Collapsible Sidebar Widget for Flutter Web Admin Panel
/// 
/// Features:
/// - Smooth width transition animation (~250ms)
/// - Expanded by default
/// - Shows icons + labels when expanded
/// - Shows only icons when collapsed
/// - Logo adapts to sidebar state
/// - Highlights active menu item
class CollapsibleSidebar extends StatelessWidget {
  final bool isCollapsed;
  final int selectedIndex;
  final List<SidebarItem> items;
  final Function(int) onItemSelected;
  final Widget? header;
  final Widget? footer;
  final VoidCallback? onLogout; // Callback for logout when collapsed

  // Sidebar width constants
  static const double expandedWidth = 260.0;
  static const double collapsedWidth = 80.0;
  static const Duration animationDuration = Duration(milliseconds: 250);

  const CollapsibleSidebar({
    super.key,
    required this.isCollapsed,
    required this.selectedIndex,
    required this.items,
    required this.onItemSelected,
    this.header,
    this.footer,
    this.onLogout,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: animationDuration,
      curve: Curves.easeInOut,
      width: isCollapsed ? collapsedWidth : expandedWidth,
      color: Colors.white,
      child: Column(
        children: [
          // Header section (Logo)
          if (header != null)
            _buildHeader(header!),
          // Navigation items
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.symmetric(vertical: 8),
              itemCount: items.length,
              itemBuilder: (context, index) {
                return _buildNavItem(context, items[index], index);
              },
            ),
          ),
          // Footer section (User info, etc.)
          if (footer != null)
            _buildFooter(footer!),
        ],
      ),
    );
  }

  /// Builds the header section with logo
  Widget _buildHeader(Widget header) {
    return AnimatedContainer(
      duration: animationDuration,
      padding: EdgeInsets.all(isCollapsed ? 16 : 24),
      decoration: BoxDecoration(
        border: Border(
          bottom: BorderSide(color: Colors.grey[200]!),
        ),
      ),
      child: isCollapsed
          ? _buildCollapsedHeader(header)
          : _buildExpandedHeader(header),
    );
  }

  /// Header when collapsed (icon only)
  Widget _buildCollapsedHeader(Widget header) {
    return Center(
      child: SizedBox(
        width: 40,
        height: 40,
        child: header,
      ),
    );
  }

  /// Header when expanded (full logo)
  Widget _buildExpandedHeader(Widget header) {
    return header;
  }

  /// Builds individual navigation item
  Widget _buildNavItem(BuildContext context, SidebarItem item, int index) {
    final isSelected = selectedIndex == index;

    return InkWell(
      onTap: () => onItemSelected(index),
      child: AnimatedContainer(
        duration: animationDuration,
        margin: EdgeInsets.symmetric(
          horizontal: isCollapsed ? 8 : 8,
          vertical: 4,
        ),
        padding: EdgeInsets.symmetric(
          horizontal: isCollapsed ? 12 : 16,
          vertical: 12,
        ),
        decoration: BoxDecoration(
          color: isSelected
              ? const Color(0xFFDDD1BC).withValues(alpha: 0.2)
              : Colors.transparent,
          borderRadius: BorderRadius.circular(8),
          border: isSelected
              ? Border.all(
                  color: const Color(0xFFDDD1BC),
                  width: 1,
                )
              : null,
        ),
        child: isCollapsed
            ? _buildCollapsedNavItem(item, isSelected)
            : _buildExpandedNavItem(item, isSelected),
      ),
    );
  }

  /// Navigation item when collapsed (icon only)
  Widget _buildCollapsedNavItem(SidebarItem item, bool isSelected) {
    return Tooltip(
      message: item.label,
      child: Icon(
        item.icon,
        color: isSelected ? Colors.black : Colors.black54,
        size: 24,
      ),
    );
  }

  /// Navigation item when expanded (icon + label)
  Widget _buildExpandedNavItem(SidebarItem item, bool isSelected) {
    return Row(
      children: [
        Icon(
          item.icon,
          color: isSelected ? Colors.black : Colors.black54,
          size: 20,
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            item.label,
            style: TextStyle(
              fontSize: 14,
              fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
              color: isSelected ? Colors.black : Colors.black54,
            ),
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }

  /// Builds the footer section
  Widget _buildFooter(Widget footer) {
    return AnimatedContainer(
      duration: animationDuration,
      padding: EdgeInsets.all(isCollapsed ? 12 : 16),
      decoration: BoxDecoration(
        border: Border(
          top: BorderSide(color: Colors.grey[200]!),
        ),
      ),
      child: isCollapsed
          ? _buildCollapsedFooter(footer)
          : _buildExpandedFooter(footer),
    );
  }

  /// Footer when collapsed (avatar only with tooltip)
  Widget _buildCollapsedFooter(Widget footer) {
    // When collapsed, show only the avatar with a logout tooltip
    // We'll extract the avatar from the footer widget
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Tooltip(
          message: 'User Profile',
          child: CircleAvatar(
            radius: 20,
            backgroundColor: const Color(0xFFDDD1BC),
            child: const Icon(Icons.person, color: Colors.black),
          ),
        ),
        const SizedBox(height: 8),
        Tooltip(
          message: 'Logout',
          child: IconButton(
            icon: const Icon(Icons.logout, size: 20),
            color: Colors.black87,
            onPressed: onLogout,
          ),
        ),
      ],
    );
  }

  /// Footer when expanded (full footer)
  Widget _buildExpandedFooter(Widget footer) {
    return footer;
  }
}

/// Model for sidebar navigation items
class SidebarItem {
  final IconData icon;
  final String label;
  final Widget screen;

  const SidebarItem({
    required this.icon,
    required this.label,
    required this.screen,
  });
}

