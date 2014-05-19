/*
 *
 */
import javax.swing.*;
import java.awt.*;
import java.awt.event.*;

public class Demo {

	private static int progress = 0;
	private static JProgressBar pbar = null;
	private static JFrame frame = null;


	private static void alert(String message) {
    	JOptionPane.showMessageDialog(frame, message);
    	//showConfirmDialog
    	//showOptionDialog
	}


	/**
	 * createComponents
	 * Create primary GUI components.
	 *
	 * @return Component The topmost panel component
	 */
	private static Component createComponents() {

		JLabel label = new JLabel("Progress Demo");
		pbar = new JProgressBar(0,100);
		//pbar.setSize(400,30);
		pbar.setValue(0);
		JButton button = new JButton("More Progress");
		button.addActionListener(new ActionListener() {
			public void actionPerformed(ActionEvent e) {
				progress = progress >= 100 ? 0 : progress + 10;
				pbar.setValue(progress);
			}
		});
		JPanel panel = new JPanel(new GridLayout(0, 1));
		panel.add(label);
		panel.add(pbar);
		panel.add(button);
		panel.setBorder(BorderFactory.createEmptyBorder(30,30,30,30));

		JLabel l1 = new JLabel("Label 1");
		JPanel p1 = new JPanel(new GridLayout(0,1));
		p1.add(l1);

		JLabel l2 = new JLabel("Label 2");
		JPanel p2 = new JPanel(new GridLayout(0,1));
		p2.add(l2);

		JTabbedPane tpane = new JTabbedPane();
		tpane.addTab("Main",null,panel,"Test 0");
		tpane.addTab("Stuff",null,p1,"Test 1");
		tpane.addTab("Dood",null,p2,"Test 2");

		return(tpane);
	}


	/**
	 * createMenuBar
	 * Builds the GUI menu bar.
	 *
	 * @return JMenuBar The constructed menu
	 */
	private static JMenuBar createMenuBar() {

		JMenuBar menuBar = new JMenuBar();

		//Build the first menu.
		JMenu menu = new JMenu("File");
		menuBar.add(menu);
		
		JMenuItem menuItem = new JMenuItem("Open");
		menu.add(menuItem);
		menuItem = new JMenuItem("Close");
		menu.add(menuItem);
		
		//a submenu
		menu.addSeparator();
		JMenu submenu = new JMenu("Submenu");
		
		menuItem = new JMenuItem("Sub Item 1");
		submenu.add(menuItem);
		menuItem = new JMenuItem("Sub Item 2");
		submenu.add(menuItem);
		menu.add(submenu);


		//Build our view menu.
		/*
		menu = new JMenu("View");
		ButtonGroup rGroup = new ButtonGroup();

		JRadioButtonMenuItem rbItem = new JRadioButtonMenuItem("System");
		rbItem.addActionListener(new ActionListener() {
			public void actionPerformed(ActionEvent e) {
				setLookAndFeel("System");
			}
		});
		rbItem.setSelected(true);
		rGroup.add(rbItem);
		menu.add(rbItem);

		rbItem = new JRadioButtonMenuItem("Metal");
		rbItem.addActionListener(new ActionListener() {
			public void actionPerformed(ActionEvent e) {
				setLookAndFeel("Metal");
			}
		});

		rGroup.add(rbItem);
		menu.add(rbItem);

		//Attach view menu.
		menuBar.add(menu);
		*/

		return(menuBar);
	}


	/**
	 * Set the GUI's look and feel.
	 */	 	
	private static void setLookAndFeel() {
		try {
			UIManager.setLookAndFeel(
				UIManager.getSystemLookAndFeelClassName()
			);
		}
		catch(Exception e) {
			System.err.println("Error setting look and feel.");
		}
	}


	/**
	 * Initalizes the entire GUI.
	 */	 	
	private static void createAndShowGUI() {

		//Set the look and feel.
		setLookAndFeel();

		//Make sure we have nice window decorations.
		JFrame.setDefaultLookAndFeelDecorated(true);

		//Create and set up the window.
		frame = new JFrame("Demo Frame");
		frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
		//frame.setLocation(100,100);
		frame.setLocationRelativeTo(null);
		//frame.setIconImage(new ImageIcon(imgURL).getImage());
		frame.setResizable(false);
		//This works if we don't "pack"
		//frame.setSize(300,800);

		//Set up and attach the content pane.
		JPanel contentPane = new JPanel(new BorderLayout());
		contentPane.add(createComponents(), BorderLayout.CENTER);
		contentPane.setOpaque(true);
		frame.setContentPane(contentPane);

		//Attach the menu bar.
		frame.setJMenuBar(createMenuBar());

		//Display the window.
		frame.pack();
		frame.setVisible(true);
	}


	/**
	 * Main function initiates the GUI.
	 */	 
	public static void main(String[] args) {
		javax.swing.SwingUtilities.invokeLater(new Runnable() {
			public void run() {
				createAndShowGUI();
			}
		});
	}
}
JScrollPane(tree);
treeView.setPreferredSize(new Dimension(100, 60));

		JPanel p1 = new JPanel();
		p1.setLayout(new BoxLayout(p1,BoxLayout.PAGE_AXIS));
		p1.add(treeView, BorderLayout.CENTER);
		p1.add(Box.createRigidArea(new Dimension(0,1)));

		JLabel aLabel = new JLabel("Node Name");
		aLabel.setVerticalTextPosition(JLabel.BOTTOM);
		aLabel.setAlignmentX(Component.CENTER_ALIGNMENT);		
		p1.add(aLabel, BorderLayout.CENTER);
		p1.add(Box.createRigidArea(new Dimension(0,1)));

		treeLabel = new JLabel(" ");
		treeLabel.setVerticalTextPosition(JLabel.BOTTOM);
		treeLabel.setAlignmentX(Component.CENTER_ALIGNMENT);		
		p1.add(treeLabel, BorderLayout.CENTER);

		JLabel l2 = new JLabel("Label 2");
		JPanel p2 = new JPanel(new GridLayout(0,1));
		p2.add(l2);

		JTabbedPane tpane = new JTabbedPane();
		tpane.addTab("Progress",null,panel,"Progress Bar");
		tpane.addTab("Tree",null,p1,"Document Tree");
		tpane.addTab("Dood",null,p2,"Test 2");

		return(tpane);
	}


	/**
	 * createMenuBar
	 * Builds the GUI menu bar.
	 *
	 * @return JMenuBar The constructed menu
	 */
	private static JMenuBar createMenuBar() {

		JMenuBar menuBar = new JMenuBar();

		//Build the first menu.
		JMenu menu = new JMenu("File");
		menuBar.add(menu);
		
		JMenuItem menuItem = new JMenuItem("Open");
		menuItem.addActionListener(new ActionListener() {
			public void actionPerformed(ActionEvent e) {
				fileChooser.setFileSelectionMode(
					JFileChooser.FILES_AND_DIRECTORIES
				);
				//DIRECTORIES_ONLY
				//FILES_ONLY
				//fileChooser.setCurrentDirectory("/");
				fileChooser.setDialogTitle("Pick Files or Directories");
				fileChooser.setMultiSelectionEnabled(true);

				//showSaveDialog(frame)
				int returnVal = fileChooser.showOpenDialog(frame);
				if(returnVal == JFileChooser.APPROVE_OPTION) {
					File files[] = fileChooser.getSelectedFiles();
					//File dir = fileChooser.getCurrentDirectory();
					//This is where a real application would open the file.
					String buffer = "";
					for(int i = 0; i < files.length; ++i) {
						buffer += files[i].getName();
						if(files[i].isDirectory()) {
							buffer += " (dir)";
						}
						else {
							buffer += " (file)";
						}
						buffer += "\n";
					}
					alert(buffer);
				}
				else {
					//Cancelled.
					alert("You did not chose a file.");
				}


				//alert("Opening...");
			}
		});
		menu.add(menuItem);
		menuItem = new JMenuItem("Close");
		menuItem.addActionListener(new ActionListener() {
			public void actionPerformed(ActionEvent e) {
				System.exit(0);
			}
		});
		menu.add(menuItem);
		
		//a submenu
		menu.addSeparator();
		JMenu submenu = new JMenu("Submenu");
		
		menuItem = new JMenuItem("Sub Item 1");
		submenu.add(menuItem);
		menuItem = new JMenuItem("Sub Item 2");
		submenu.add(menuItem);
		menu.add(submenu);


		//Build our view menu.
		/*
		menu = new JMenu("View");
		ButtonGroup rGroup = new ButtonGroup();

		JRadioButtonMenuItem rbItem = new JRadioButtonMenuItem("System");
		rbItem.addActionListener(new ActionListener() {
			public void actionPerformed(ActionEvent e) {
				setLookAndFeel("System");
			}
		});
		rbItem.setSelected(true);
		rGroup.add(rbItem);
		menu.add(rbItem);

		rbItem = new JRadioButtonMenuItem("Metal");
		rbItem.addActionListener(new ActionListener() {
			public void actionPerformed(ActionEvent e) {
				setLookAndFeel("Metal");
			}
		});

		rGroup.add(rbItem);
		menu.add(rbItem);

		//Attach view menu.
		menuBar.add(menu);
		*/

		return(menuBar);
	}


	/**
	 * Set the GUI's look and feel.
	 */	 	
	private static void setLookAndFeel() {
		try {
			UIManager.setLookAndFeel(
				UIManager.getSystemLookAndFeelClassName()
			);
		}
		catch(Exception e) {
			System.err.println("Error setting look and feel.");
		}
	}


	/**
	 * Initalizes the entire GUI.
	 */	 	
	private static void createAndShowGUI() {

		//Set the look and feel.
		setLookAndFeel();

		//Make sure we have nice window decorations.
		JFrame.setDefaultLookAndFeelDecorated(true);

		//Create and set up the window.
		frame = new JFrame("Demo");
		frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
		//frame.setLocation(100,100);
		frame.setLocationRelativeTo(null);
		//frame.setIconImage(new ImageIcon(imgURL).getImage());
		frame.setResizable(false);
		//This works if we don't "pack"
		//frame.setSize(300,800);

		//Set up and attach the content pane.
		JPanel contentPane = new JPanel(new BorderLayout());
		contentPane.add(createComponents(), BorderLayout.CENTER);
		contentPane.setOpaque(true);
		frame.setContentPane(contentPane);

		//Attach the menu bar.
		frame.setJMenuBar(createMenuBar());

		//Display the window.
		frame.pack();
		frame.setVisible(true);

		fileChooser = new JFileChooser();
	}


	/**
	 * Main function initiates the GUI.
	 */	 
	public static void main(String[] args) {
		javax.swing.SwingUtilities.invokeLater(new Runnable() {
			public void run() {
				createAndShowGUI();
			}
		});
	}
}
